/* =====================================================================
   HOME CARE — MAIN SCRIPT (DO NOT EDIT)
   Menu, popup, forms (validation + AJAX), card filters, click tracking
   ===================================================================== */
(function () {
  'use strict';
  var body = document.body;
  var $ = function (s, el) { return (el || document).querySelector(s); };
  var $$ = function (s, el) { return Array.prototype.slice.call((el || document).querySelectorAll(s)); };

  /* ---------- Mobile menu ---------- */
  var burger = $('.burger');
  var mnav = $('#mnav');
  function openMenu() { body.classList.add('menu-open'); if (burger) burger.setAttribute('aria-expanded', 'true'); if (mnav) mnav.setAttribute('aria-hidden', 'false'); }
  function closeMenu() { body.classList.remove('menu-open'); if (burger) burger.setAttribute('aria-expanded', 'false'); if (mnav) mnav.setAttribute('aria-hidden', 'true'); }
  if (burger) burger.addEventListener('click', openMenu);
  $$('[data-close-menu]').forEach(function (el) { el.addEventListener('click', closeMenu); });

  /* ---------- Popup ---------- */
  var pop = $('#hcPop');
  var lastFocus = null;
  function openPopup(item, details) {
    if (!pop) return;
    closeMenu();
    var title = $('#popTitle', pop);
    var forBox = $('.pop-for', pop);
    var form = $('form', pop);
    item = item || '';
    if (item) {
      title.textContent = (title.getAttribute('data-prefix') || 'Book') + ' ' + item;
      $('.pop-item', pop).textContent = item;
      $('.pop-det', pop).textContent = details || '';
      $('.pop-det', pop).style.display = details ? '' : 'none';
      forBox.hidden = false;
    } else {
      title.textContent = title.getAttribute('data-general');
      forBox.hidden = true;
    }
    if (form) {
      form.elements.enquiry_item.value = item;
      $$('select[data-select]', form).forEach(function (sel) {
        if (!item) { sel.value = ''; return; }
        var found = false;
        Array.prototype.forEach.call(sel.options, function (o) { if (o.value === item || o.text === item) { found = true; sel.value = o.value; } });
        if (!found) {
          var o = document.createElement('option'); o.text = item; o.value = item;
          sel.add(o, sel.options[1] || null); sel.value = item;
        }
      });
      $$('.field.err', form).forEach(function (f) { f.classList.remove('err'); });
      var err = $('.form-error', form); if (err) err.hidden = true;
    }
    lastFocus = document.activeElement;
    pop.classList.add('open');
    pop.setAttribute('aria-hidden', 'false');
    body.classList.add('pop-open');
    setTimeout(function () { var f = form && form.querySelector('input:not([type=hidden]):not([tabindex="-1"])'); if (f && window.innerWidth > 640) f.focus(); }, 250);
  }
  function closePopup() {
    if (!pop || !pop.classList.contains('open')) return;
    pop.classList.remove('open');
    pop.setAttribute('aria-hidden', 'true');
    body.classList.remove('pop-open');
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }
  document.addEventListener('click', function (e) {
    var b = e.target.closest ? e.target.closest('[data-popup]') : null;
    if (b) { e.preventDefault(); openPopup(b.getAttribute('data-item'), b.getAttribute('data-details')); }
  });
  if (pop) {
    pop.addEventListener('click', function (e) { if (e.target === pop || e.target.closest('[data-close-pop]')) closePopup(); });
  }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' || e.key === 'Esc') { closePopup(); closeMenu(); } });

  /* ---------- Mobile number field ---------- */
  function cleanMobile(v) {
    var d = String(v || '').replace(/\D/g, '');
    if (d.length > 10 && d.indexOf('91') === 0) d = d.slice(2);
    if (d.length > 10 && d.charAt(0) === '0') d = d.slice(1);
    if (d.length === 11 && d.charAt(0) === '0') d = d.slice(1);
    return d.slice(0, 10);
  }
  function validMobile(v) { return /^[6-9]\d{9}$/.test(v); }
  $$('input[data-mobile]').forEach(function (inp) {
    // maxlength 10 would cut "+91 98765 43210" before cleaning, so paste is handled here
    inp.addEventListener('paste', function (e) {
      var t = (e.clipboardData || window.clipboardData).getData('text');
      e.preventDefault();
      inp.value = cleanMobile(t);
      inp.dispatchEvent(new Event('input'));
    });
    inp.addEventListener('input', function () {
      var c = cleanMobile(inp.value);
      if (c !== inp.value) inp.value = c;
      if (validMobile(c)) inp.closest('.field').classList.remove('err');
    });
    inp.addEventListener('blur', function () {
      if (inp.value !== '') inp.closest('.field').classList.toggle('err', !validMobile(inp.value));
    });
  });

  /* ---------- Form validation + AJAX submit ---------- */
  function today() { var d = new Date(); d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); return d.toISOString().slice(0, 10); }
  $$('input[type=date]').forEach(function (i) { i.min = today(); });

  function checkField(el) {
    var v = (el.value || '').trim();
    var ok = true;
    if (el.required && v === '') ok = false;
    else if (el.hasAttribute('data-mobile') && v !== '') ok = validMobile(v);
    else if (el.type === 'email' && v !== '') ok = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v);
    else if (el.type === 'date' && v !== '') ok = v >= today();
    var f = el.closest('.field');
    if (f) f.classList.toggle('err', !ok);
    return ok;
  }

  $$('form.hc-form').forEach(function (form) {
    $$('input,select,textarea', form).forEach(function (el) {
      if (el.type === 'hidden' || el.name === 'website') return;
      el.addEventListener('change', function () { if (el.closest('.field').classList.contains('err')) checkField(el); });
    });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var ok = true, first = null;
      if (form.elements.page_url) form.elements.page_url.value = location.href;
      $$('input,select,textarea', form).forEach(function (el) {
        if (el.type === 'hidden' || el.name === 'website') return;
        if (el.hasAttribute('data-mobile')) el.value = cleanMobile(el.value);
        if (!checkField(el)) { ok = false; if (!first) first = el; }
      });
      var errBox = $('.form-error', form);
      if (!ok) { if (first) first.focus(); return; }

      var btn = $('button[type=submit]', form);
      var txt = btn.innerHTML;
      btn.disabled = true;
      btn.textContent = btn.getAttribute('data-sending') || 'Sending…';
      if (errBox) errBox.hidden = true;

      var fd = new FormData(form);
      fd.append('ajax', '1');
      fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.ok && res.redirect) {
            push('form_submit', { form_name: form.elements.form_name ? form.elements.form_name.value : '' });
            window.location.href = res.redirect;
          } else {
            throw new Error(res && res.message ? res.message : 'Error');
          }
        })
        .catch(function (err) {
          btn.disabled = false;
          btn.innerHTML = txt;
          if (errBox) {
            errBox.textContent = err && err.message && err.message !== 'Failed to fetch' && err.message.indexOf('JSON') === -1
              ? err.message : 'Sorry, something went wrong. Please try again or call us.';
            errBox.hidden = false;
          }
        });
    });
  });

  /* ---------- Card filters ---------- */
  $$('.filters').forEach(function (bar) {
    var grid = bar.nextElementSibling;
    bar.addEventListener('click', function (e) {
      var b = e.target.closest('button'); if (!b) return;
      $$('button', bar).forEach(function (x) { x.classList.toggle('on', x === b); });
      var f = b.getAttribute('data-filter');
      $$('.card', grid).forEach(function (c) { c.classList.toggle('hide', f !== '' && c.getAttribute('data-cat') !== f); });
    });
  });

  /* ---------- Click tracking (Google Tag Manager / GA4 / Google Ads) ---------- */
  function push(event, data) {
    try {
      window.dataLayer = window.dataLayer || [];
      var o = { event: event }; for (var k in data) o[k] = data[k];
      window.dataLayer.push(o);
      if (typeof window.gtag === 'function') window.gtag('event', event, data || {});
    } catch (e) {}
  }
  document.addEventListener('click', function (e) {
    var a = e.target.closest ? e.target.closest('[data-track]') : null;
    if (a) push(a.getAttribute('data-track') + '_click', { link_url: a.href || '', page_path: location.pathname });
  });
})();
