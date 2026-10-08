<?php /* THANK YOU PAGE DESIGN — text comes from Admin → Pages → Thank You */
$clean = function ($v) { return trim(mb_substr(strip_tags((string)$v), 0, 80, 'UTF-8')); };
$name = $clean(request()->query('name', ''));
$item = $clean(request()->query('item', ''));
$parts = preg_split('/\s+/', $name);
$first = $name !== '' ? $parts[0] : '';
hc_layout_start();
?>
<section class="sec"><div class="wrap narrow">
  <div class="ty-box">
    <div class="ty-tick"><?= hc_icon('check') ?></div>
    <h2><?= hc_e($name !== '' ? str_replace('{name}', $first, c('heading_name', 'Thank You, {name}!')) : c('heading_general', 'Thank You!')) ?></h2>
    <p class="ty-msg"><?= hc_e($item !== '' ? str_replace('{item}', $item, c('message_item', 'We received your enquiry for {item}.')) : c('message_general', 'We received your enquiry.')) ?></p>
    <?php if (c('small_line', '') !== ''): ?><p class="ty-small"><?= hc_e(c('small_line')) ?></p><?php endif; ?>
    <div class="ty-btns">
      <a class="btn btn-ty" href="<?= hc_e(hc_tel()) ?>" data-track="call" style="background:<?= hc_e(s('thankyou.call_bg', '#1f5c99')) ?>;color:<?= hc_e(s('thankyou.call_color', '#fff')) ?>"><?= hc_icon('phone') ?><span><?= hc_e(s('thankyou.call_text', 'CALL NOW')) ?> – <?= hc_e(s('mobile')) ?></span></a>
      <?= hc_wa_btn('btn btn-wa', $item) ?>
      <a class="btn btn-outline" href="<?= hc_e(hc_url('')) ?>"><?= hc_e(s('thankyou.home_text', 'Back to Home')) ?></a>
    </div>
  </div>
</div></section>
<?php hc_layout_end(false); ?>
