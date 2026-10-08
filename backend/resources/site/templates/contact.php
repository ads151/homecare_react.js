<?php /* CONTACT PAGE DESIGN — text comes from Admin → Pages → Contact */ hc_layout_start(); ?>
<section class="sec"><div class="wrap">
  <?= hc_sec_head(1) ?>
  <?= hc_contact_cards_html() ?>
  <div class="contact-main two">
    <div class="form-card">
      <h2><?= hc_e(c('form_heading', 'Send Us a Message')) ?></h2>
      <?php if (c('form_text', '') !== ''): ?><p class="sub"><?= hc_e(c('form_text')) ?></p><?php endif; ?>
      <?= hc_form('contactForm', 'Contact Page Form') ?>
    </div>
    <?php if (on(c('map_show', true))): ?><?= hc_map_html(c('map_embed', '')) ?><?php endif; ?>
  </div>
</div></section>
<?php hc_layout_end(on(c('cta_show', true))); ?>
