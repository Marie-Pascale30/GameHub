<?php if ($summary['average'] !== null): ?>
    <strong class="detail__avg"><?= format_rating($summary['average']) ?></strong>
    <span><?= stars($summary['average']) ?><small><?= plural($summary['count'], 'avis', 'avis') ?></small></span>
<?php else: ?>
    <span class="muted">Pas encore de note — soyez le premier à donner votre avis.</span>
<?php endif; ?>
