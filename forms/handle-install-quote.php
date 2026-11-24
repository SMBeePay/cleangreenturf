<?php
/**
 * Handler for the short turf-installation quote form on /turf-installation
 * (includes/install-quote-form.php). Same pipeline as forms/handle-quote.php:
 *
 *   spam guard -> validation -> owner email (blocking, photos attached)
 *   -> customer confirmation (best-effort) -> Zoho Turf Installation Deal
 *   (best-effort) -> redirect to the estimate scheduler with a banner, so
 *   the visitor can also book an in-person visit if they want.
 *
 * Photos are optional. A bad or oversized photo never costs the lead: it's
 * skipped and the owner email says so.
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/zoho-crm.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/spam-guard.php';
require_once __DIR__ . '/../includes/attribution.php';

$businessInfo = require __DIR__ . '/../config/business-info.php';
$mailConfig = require __DIR__ . '/../config/mail.php';
$zohoConfig = require __DIR__ . '/../config/zoho.php';

const INSTALL_FORM_PAGE = '/turf-installation';
const INSTALL_SUCCESS_URL = '/schedule-turf-installation-estimate?quote=sent';
const CALCULATOR_SUCCESS_URL = '/schedule-turf-installation-estimate?quote=sent&from=calculator';
const INSTALL_MAX_PHOTOS = 3;
const INSTALL_MAX_PHOTO_BYTES = 8 * 1024 * 1024;

// The cost calculator posts here too (form_source=calculator): email is
// optional there, city comes from its dropdown, and the size and price
// range it showed come along as calc_* fields.
$isCalculator = ($_POST['form_source'] ?? '') === 'calculator';
const CALCULATOR_PAGE = '/turf-installation/cost-calculator';

function install_fail(string $reason): never {
    global $isCalculator;
    header('Location: ' . ($isCalculator
        ? CALCULATOR_PAGE . '?error=' . urlencode($reason) . '#calc-error'
        : INSTALL_FORM_PAGE . '?error=' . urlencode($reason) . '#install-quote'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . INSTALL_FORM_PAGE);
    exit;
}

// Photos bigger than PHP's post_max_size empty $_POST entirely; say why.
if ($_POST === [] && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    install_fail('upload_too_large');
}

if (!empty($_POST['website'])) {
    spam_guard_log('spam-blocked.log', 'honeypot filled (install form)', $_POST);
    header('Location: ' . INSTALL_SUCCESS_URL);
    exit;
}
$timingStatus = spam_guard_timing_status((string)($_POST['form_ts'] ?? ''));
if ($timingStatus === 'invalid' || $timingStatus === 'too_fast') {
    spam_guard_log('spam-blocked.log', "timing: $timingStatus (install form)", $_POST);
    header('Location: ' . INSTALL_SUCCESS_URL);
    exit;
}

function install_clean_line(string $value): string {
    return trim(preg_replace('/[\r\n]+/', ' ', $value));
}

$name = install_clean_line((string)($_POST['name'] ?? ''));
$phone = install_clean_line((string)($_POST['phone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$city = install_clean_line((string)($_POST['city'] ?? ''));
$turfSize = install_clean_line((string)($_POST['turf_size'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));
if ($isCalculator) {
    $calcArea = (int)($_POST['calc_area'] ?? 0);
    $turfSize = $calcArea > 0 ? number_format($calcArea) . ' sq ft (from calculator)' : '';
    $notes = 'Cost calculator: ' . install_clean_line((string)($_POST['calc_use'] ?? '')) . ', '
        . install_clean_line((string)($_POST['calc_site'] ?? '')) . '. Range shown: '
        . install_clean_line((string)($_POST['calc_result'] ?? '')) . '.';
}

if ($name === '' || $phone === '' || $city === '' || $turfSize === '' || (!$isCalculator && $email === '')) {
    install_fail('missing_fields');
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    install_fail('invalid_email');
}
if (!$isCalculator && !looks_like_real_city($city)) {
    install_fail('invalid_city');
}

$attribution = attribution_from_post();

// --- Photos (optional) ---
$attachments = [];
$skippedPhotos = 0;
$allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic', 'image/heif' => 'heif'];
if (!empty($_FILES['photos']['name']) && is_array($_FILES['photos']['name'])) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    foreach ($_FILES['photos']['name'] as $i => $originalName) {
        if (($_FILES['photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $tmp = (string)$_FILES['photos']['tmp_name'][$i];
        $ok = $_FILES['photos']['error'][$i] === UPLOAD_ERR_OK
            && is_uploaded_file($tmp)
            && filesize($tmp) <= INSTALL_MAX_PHOTO_BYTES
            && count($attachments) < INSTALL_MAX_PHOTOS;
        $type = $ok ? (string)$finfo->file($tmp) : '';
        if (!$ok || !isset($allowedTypes[$type])) {
            $skippedPhotos++;
            continue;
        }
        $attachments[] = ['path' => $tmp, 'name' => 'yard-photo-' . (count($attachments) + 1) . '.' . $allowedTypes[$type]];
    }
}

// Calculator notes are machine-written, so only the name is checked there.
$spamSignals = spam_guard_content_signals($isCalculator ? '' : $notes, $name, $email);
$isSuspectedSpam = $spamSignals !== [];
if ($isSuspectedSpam) {
    spam_guard_log('spam-flagged.log', 'flagged (install form): ' . implode('; ', $spamSignals), $_POST);
}
$isLargeJob = $turfSize === '2500+ sq ft';

$details = "Name: $name\n"
    . "Phone: $phone\n"
    . 'Email: ' . ($email !== '' ? $email : 'Not given') . "\n"
    . "City: $city\n"
    . "Approx. Square Footage: $turfSize\n"
    . 'Photos: ' . count($attachments) . ' attached' . ($skippedPhotos ? ", $skippedPhotos skipped (wrong type or over 8 MB)" : '') . "\n"
    . 'Notes: ' . ($notes !== '' ? $notes : 'None') . "\n";

$subject = ($isSuspectedSpam ? '[Possible Spam] ' : '')
    . ($isLargeJob ? '⚠️ Large Install (2,500+ sq ft) — ' : ($isCalculator ? 'New Calculator Lead — ' : 'New Turf Installation Quote — ')) . $name;
$body = 'New turf installation quote request from cleangreenturf.com' . ($isCalculator ? CALCULATOR_PAGE : INSTALL_FORM_PAGE) . "\n\n"
    . ($isSuspectedSpam ? '*** Flagged as possible spam (' . implode('; ', $spamSignals) . "). Not added to Zoho, no auto-reply sent. ***\n\n" : '')
    . $details . "\n" . attribution_text_block($attribution);

if (!send_transactional_email($mailConfig, $businessInfo['email'], $businessInfo['name'], $subject, $body, $email !== '' ? $email : null, $name, $attachments)) {
    error_log('Install quote form failed to send for ' . $email);
    record_failed_lead_email('Install quote form', $subject, $body);
    install_fail('send_failed');
}

if ($isSuspectedSpam) {
    header('Location: ' . ($isCalculator ? CALCULATOR_SUCCESS_URL : INSTALL_SUCCESS_URL));
    exit;
}

$phoneDisplay = $businessInfo['regions']['tx']['phone_display'];
$customerBody = "Hi $name,\n\n"
    . "Thanks for asking Clean Green Turf about a turf installation. Here's what you sent:\n\n"
    . "City: $city\nApprox. Size: $turfSize\n"
    . ($notes !== '' ? "Notes: $notes\n" : '')
    . "\nWe'll come back with a price range for your yard. If you'd like us to come out and measure, you can pick a time here: "
    . $businessInfo['domain'] . "/schedule-turf-installation-estimate\n\n"
    . "Talk soon,\n{$businessInfo['name']}\n$phoneDisplay\n";
if ($email !== '' && !send_transactional_email($mailConfig, $email, $name, 'Thanks for Reaching Out to Clean Green Turf!', $customerBody)) {
    error_log('Install quote customer confirmation failed to send for ' . $email);
}

zoho_push_lead($zohoConfig, $name, $email, $phone, [
    'Deal_Name' => ($isLargeJob ? '⚠️ ' : '') . "$name — Turf Installation " . ($isCalculator ? 'Calculator Lead' : 'Quote') . " ($city)",
    'Pipeline' => ZOHO_PIPELINE_INSTALLATION,
    'Stage' => ZOHO_STAGE_INSTALLATION_NEW,
    'Closing_Date' => date('Y-m-d', strtotime('+30 days')),
    'Description' => $details . "\n" . attribution_text_block($attribution),
    'Lead_Channel' => ZOHO_LEAD_CHANNEL_QUOTE_FORM,
    'Lead_Source' => attribution_lead_source($attribution, false),
    'Landing_Page_URL' => zoho_landing_page_url($businessInfo, $isCalculator ? CALCULATOR_PAGE : INSTALL_FORM_PAGE),
    'Service_Line' => ZOHO_SERVICE_LINE_INSTALLATION,
] + attribution_zoho_fields($attribution, $zohoConfig));

header('Location: ' . ($isCalculator ? CALCULATOR_SUCCESS_URL : INSTALL_SUCCESS_URL));
exit;
