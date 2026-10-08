<?php /* ABOUT PAGE DESIGN — text comes from Admin → Pages → About */ hc_layout_start(); ?>
<?php if (hc_show(1)): ?>
<section class="sec"><div class="wrap two about-intro">
  <div class="about-img">
    <?php if (c('section1_image', '') !== ''): ?><img src="<?= hc_e(hc_img(c('section1_image'))) ?>" alt="<?= hc_e(c('section1_image_alt', '') !== '' ? c('section1_image_alt') : hc_plain_title(c('section1_heading', ''))) ?>" loading="lazy" decoding="async" width="900" height="700"><?php endif; ?>
  </div>
  <div>
    <?= hc_sec_head(1, false) ?>
    <?= hc_ticks_html(c('section1_items', array())) ?>
    <?php if (c('section1_button_text', '') !== ''): ?><div class="mt"><?= hc_button(c('section1_button_text'), c('section1_button_link', 'popup'), 'btn btn-main') ?></div><?php endif; ?>
  </div>
</div></section>
<?php endif; ?>

<?php if (hc_show(2)): ?><?= hc_stats_html(c('section2_items', array()), true) ?><?php endif; ?>

<?php if (hc_show(3)): ?>
<section class="sec"><div class="wrap">
  <?= hc_sec_head(3) ?>
  <?= hc_boxes_html(c('section3_items', array())) ?>
</div></section>
<?php endif; ?>

<?php if (hc_show(4)): ?>
<section class="sec bg-light"><div class="wrap">
  <?= hc_sec_head(4) ?>
  <?= hc_photo_cards_html(c('section4_items', array())) ?>
</div></section>
<?php endif; ?>
<?php hc_layout_end(on(c('cta_show', true))); ?>
