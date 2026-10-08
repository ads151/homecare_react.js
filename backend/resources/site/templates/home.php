<?php /* HOME PAGE DESIGN — text comes from Admin → Pages → Home */ hc_layout_start(); ?>
<section class="hero">
  <?= hc_hero_picture(c('hero_image', ''), c('hero_image_mobile', ''), c('hero_image_alt', '') !== '' ? c('hero_image_alt') : hc_plain_title(c('hero_title', ''))) ?>
  <div class="wrap">
    <div class="hero-txt">
      <?php if (c('hero_badge', '') !== ''): ?><span class="pill"><?= hc_e(c('hero_badge')) ?></span><?php endif; ?>
      <h1><?= hc_title(c('hero_title', '') !== '' ? c('hero_title') : s('business_name')) ?></h1>
      <?= hc_paras(c('hero_text', ''), 'lead') ?>
      <?= hc_ticks_html(c('hero_points', array()), 'ticks') ?>
      <div class="hero-btns">
        <?= hc_call_btn('btn btn-call', c('hero_call_text', '') !== '' ? c('hero_call_text') : s('buttons.call_text', 'Call Now')) ?>
        <?= hc_wa_btn() ?>
      </div>
      <?php if (c('hero_trust_line', '') !== ''): ?>
      <p class="trust"><span class="stars"><?= str_repeat(hc_icon('star'), 5) ?></span> <?= hc_e(c('hero_trust_line')) ?></p>
      <?php endif; ?>
    </div>
    <?php if (on(c('hero_form_show', true))): ?>
    <div class="form-card" id="book">
      <h2><?= hc_e(c('hero_form_heading', 'Get a Free Call Back')) ?></h2>
      <?php if (c('hero_form_text', '') !== ''): ?><p class="sub"><?= hc_title(c('hero_form_text')) ?></p><?php endif; ?>
      <?= hc_form('heroForm', 'Home Page – Banner Form', true) ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if (hc_show(1)): ?><?= hc_stats_html(c('section1_items', array()), false) ?><?php endif; ?>

<?php if (hc_show(2)): ?>
<section class="sec" id="services"><div class="wrap">
  <?= hc_sec_head(2) ?>
  <?= hc_cards_grid(on(c('section2_filter', false)), (int)c('section2_limit', 0)) ?>
  <?php if (c('section2_button_text', '') !== ''): ?>
  <div class="center mt"><?= hc_button(c('section2_button_text'), c('section2_button_link', ''), 'btn btn-outline') ?></div>
  <?php endif; ?>
</div></section>
<?php endif; ?>

<?php if (hc_show(3)): ?>
<section class="sec bg-light" id="how-it-works"><div class="wrap">
  <?= hc_sec_head(3) ?>
  <?= hc_steps_html(c('section3_items', array())) ?>
</div></section>
<?php endif; ?>

<?php if (hc_show(4)): ?>
<section class="sec why" id="why-us"><div class="wrap two">
  <div class="why-img">
    <?php if (c('section4_image', '') !== ''): ?><img src="<?= hc_e(hc_img(c('section4_image'))) ?>" alt="<?= hc_e(c('section4_image_alt', '') !== '' ? c('section4_image_alt') : hc_plain_title(c('section4_heading', ''))) ?>" loading="lazy" decoding="async" width="800" height="860"><?php endif; ?>
    <?php if (c('section4_badge_big', '') !== ''): ?><div class="float"><b><?= hc_e(c('section4_badge_big')) ?></b><span><?= hc_e(c('section4_badge_text', '')) ?></span></div><?php endif; ?>
  </div>
  <div>
    <?= hc_sec_head(4, false) ?>
    <?= hc_feat_html(c('section4_items', array())) ?>
    <?php if (c('section4_button_text', '') !== ''): ?><div class="mt"><?= hc_button(c('section4_button_text'), c('section4_button_link', 'popup'), 'btn btn-main') ?></div><?php endif; ?>
  </div>
</div></section>
<?php endif; ?>

<?php if (hc_show(5)): ?><?= hc_cta_band(c('section5_heading', '') !== '' ? c('section5_heading') : s('cta_heading'), c('section5_text', '') !== '' ? c('section5_text') : s('cta_text')) ?><?php endif; ?>

<?php if (hc_show(6)): ?>
<section class="sec" id="areas"><div class="wrap">
  <?= hc_sec_head(6) ?>
  <?= hc_areas_html(c('section6_areas', array())) ?>
</div></section>
<?php endif; ?>

<?php if (hc_show(7)): ?>
<section class="sec bg-light" id="testimonials"><div class="wrap">
  <?= hc_sec_head(7) ?>
  <?= hc_testimonials_html(c('section7_items', array())) ?>
</div></section>
<?php endif; ?>

<?php if (hc_show(8)): ?>
<section class="sec" id="faq"><div class="wrap">
  <?= hc_sec_head(8) ?>
  <?= hc_faq_html(c('section8_items', array())) ?>
</div></section>
<?php endif; ?>

<?php if (hc_show(9)): ?>
<section class="sec final" id="enquiry"><div class="wrap two">
  <div>
    <?php if (c('section9_small', '') !== ''): ?><span class="eyebrow light"><?= hc_e(c('section9_small')) ?></span><?php endif; ?>
    <h2><?= hc_title(c('section9_heading', '')) ?></h2>
    <?= hc_paras(c('section9_text', '')) ?>
    <ul class="contact-list">
      <li><span class="ci"><?= hc_icon('phone') ?></span><span><small>Call / WhatsApp</small><a href="<?= hc_e(hc_tel()) ?>" data-track="call"><?= hc_e(s('mobile')) ?></a></span></li>
      <li><span class="ci"><?= hc_icon('mail') ?></span><span><small>Email</small><a href="mailto:<?= hc_e(s('email')) ?>"><?= hc_e(s('email')) ?></a></span></li>
      <li><span class="ci"><?= hc_icon('pin') ?></span><span><small>Address</small><b><?= hc_e(s('address')) ?></b></span></li>
    </ul>
  </div>
  <div class="form-card">
    <h2><?= hc_e(c('section9_form_heading', 'Request a Call Back')) ?></h2>
    <?php if (c('section9_form_text', '') !== ''): ?><p class="sub"><?= hc_e(c('section9_form_text')) ?></p><?php endif; ?>
    <?= hc_form('homeForm', 'Home Page – Bottom Form') ?>
  </div>
</div></section>
<?php endif; ?>
<?php hc_layout_end(false); ?>
