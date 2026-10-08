<?php /* PAGE BUILDER DESIGN — sections added from Admin → Pages */ hc_layout_start(); ?>
<?= hc_blocks(c('blocks', array())) ?>
<?php hc_layout_end(on(c('cta_show', true))); ?>
