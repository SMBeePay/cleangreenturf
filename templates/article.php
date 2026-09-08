<?php
/** Article/blog template — adds a published-date byline above the content. */
?>
<article class="article">
    <?php if (!empty($route['date_published'])): ?>
    <p class="article__meta">Published <time datetime="<?= htmlspecialchars($route['date_published']) ?>">
        <?= htmlspecialchars(date('F j, Y', strtotime($route['date_published']))) ?>
    </time></p>
    <?php endif; ?>
    <?php require __DIR__ . '/../content/pages/' . $route['slug'] . '.php'; ?>
</article>
