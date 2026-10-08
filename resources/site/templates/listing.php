<?php /* ALL SERVICES DESIGN — cards come from Admin → Services */ hc_layout_start(); ?>
<section class="sec"><div class="wrap">
  <?= hc_sec_head(1) ?>
  <?= hc_cards_grid(on(c('filter_show', true)), 0, c('category_filter', '')) ?>

  <?php if (hc_show(2)): ?>
  <div class="custom-box">
    <div><h2><?= hc_e(c('section2_heading', '')) ?></h2><?= hc_paras(c('section2_text', '')) ?></div>
    <div class="cta-btns"><?= hc_button(c('section2_button_text', 'Enquire Now'), c('section2_button_link', 'popup'), 'btn btn-main', c('section2_item', '')) ?><?= hc_wa_btn('btn btn-wa', c('section2_item', '')) ?></div>
  </div>
  <?php endif; ?>
</div></section>
<?php hc_layout_end(on(c('cta_show', true))); ?>
