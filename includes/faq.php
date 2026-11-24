<?php
/**
 * FAQ block: renders a visible accordion AND registers the same Q&A for
 * FAQPage JSON-LD (includes/schema.php), so the schema always matches the
 * visible text word for word. Answers are in the page HTML (<details>),
 * not loaded on click.
 *
 *   render_faq([
 *       ['How long does it take?', '<p>About a week.</p>'],
 *       fact_ready('warranty_text') ? ['Warranty?', '<p>...</p>'] : null, // null = skipped
 *   ]);
 */
declare(strict_types=1);

function render_faq(array $items): void {
    $items = array_values(array_filter($items));
    if ($items === []) {
        return;
    }
    echo "<div class=\"faq\">\n";
    foreach ($items as [$question, $answerHtml]) {
        $GLOBALS['pageFaq'][] = [
            'q' => $question,
            'a' => faq_plain_text($answerHtml),
        ];
        echo '<details class="faq__item"><summary>' . htmlspecialchars($question, ENT_QUOTES) . "</summary>\n"
            . '<div class="faq__answer">' . $answerHtml . "</div></details>\n";
    }
    echo "</div>\n";
}

function faq_plain_text(string $html): string {
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/', ' ', $text));
}
