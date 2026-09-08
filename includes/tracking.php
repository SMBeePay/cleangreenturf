<?php
/**
 * Analytics/tracking — preserved exactly from the live site (see
 * docs/business-info.md). Only rendered once, here, so it can never be
 * accidentally duplicated on a page.
 */
$ga4Id = $businessInfo['tracking']['ga4_id'];
$hubspotPortalId = $businessInfo['tracking']['hubspot_portal_id'];
?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga4Id) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= htmlspecialchars($ga4Id) ?>');
</script>
<!-- Start of HubSpot Embed Code -->
<script type="text/javascript" id="hs-script-loader" async defer src="//js-na2.hs-scripts.com/<?= htmlspecialchars($hubspotPortalId) ?>.js"></script>
<!-- End of HubSpot Embed Code -->
