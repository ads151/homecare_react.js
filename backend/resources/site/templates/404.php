<?php /* 404 PAGE DESIGN */ hc_layout_start(); ?>
<section class="sec"><div class="wrap narrow">
  <div class="ty-box">
    <div class="ty-tick err">404</div>
    <h2>Oops! This page is not available.</h2>
    <p class="ty-msg">The link may be old or typed wrongly. Please go back to the home page, or call us — we are happy to help.</p>
    <div class="ty-btns">
      <a class="btn btn-main" href="<?= hc_e(hc_url('')) ?>">Go to Home Page</a>
      <?= hc_call_btn('btn btn-call', s('buttons.call_text', 'Call Now') . ' ' . s('mobile')) ?>
    </div>
  </div>
</div></section>
<?php hc_layout_end(false); ?>
