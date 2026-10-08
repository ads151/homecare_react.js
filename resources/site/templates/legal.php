<?php /* LONG TEXT PAGE DESIGN (Privacy / Terms) */ hc_layout_start(); ?>
<section class="sec"><div class="wrap narrow legal">
  <?php if (c('last_updated', '') !== ''): ?><p class="updated">Last updated: <?= hc_e(c('last_updated')) ?></p><?php endif; ?>
  <?= hc_rich(c('body', '')) ?>
</div></section>
<?php hc_layout_end(on(c('cta_show', true))); ?>
