<?php
/* =====================================================================
   WEBSITE ENGINE — helper functions used by the page designs.
   Content comes from the database (Admin panel). The design (HTML/CSS)
   is the same as the original static website.
   ===================================================================== */

use App\Models\Service;

/* ---------------------------------------------------------------------
   SETTINGS / CONTENT READERS
   --------------------------------------------------------------------- */
function s($key, $default = '')
{
    $set = $GLOBALS['HC']['settings'];
    if (strpos($key, '.') !== false) {
        list($a, $b) = explode('.', $key, 2);
        return (isset($set[$a]) && is_array($set[$a]) && array_key_exists($b, $set[$a]) && $set[$a][$b] !== null) ? $set[$a][$b] : $default;
    }
    return (array_key_exists($key, $set) && $set[$key] !== null) ? $set[$key] : $default;
}

function c($key, $default = '')
{
    $con = $GLOBALS['HC']['content'];
    return hc_fill((array_key_exists($key, $con) && $con[$key] !== null) ? $con[$key] : $default);
}

// {phone} {email} {address} {business} in any text = value from settings
function hc_fill($v)
{
    if (is_array($v)) { return array_map('hc_fill', $v); }
    if (!is_string($v) || strpos($v, '{') === false) { return $v; }
    return str_replace(
        array('{phone}', '{email}', '{address}', '{business}', '{year}'),
        array((string)s('mobile'), (string)s('email'), (string)s('address'), (string)s('business_name'), date('Y')),
        $v
    );
}

function on($value)
{
    return $value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'yes' || $value === 'on';
}

function hc_e($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function hc_list($v)
{
    return is_array($v) ? array_values($v) : array();
}

/** Read a value from a list row that is either ['key' => …] or [0 => …]. */
function hc_i($row, $key, $index = null, $default = '')
{
    if (is_string($row) && ($index === 0 || $index === null)) { return $row; }
    if (!is_array($row)) { return $default; }
    if (array_key_exists($key, $row) && $row[$key] !== null) { return $row[$key]; }
    if ($index !== null && array_key_exists($index, $row) && $row[$index] !== null) { return $row[$index]; }
    return $default;
}

/** Simple list of strings from a repeater (['a','b'] or [['text'=>'a']]). */
function hc_strings($v, $key = 'text')
{
    $out = array();
    foreach (hc_list($v) as $row) {
        $t = is_array($row) ? hc_i($row, $key, 0, '') : $row;
        if (is_array($t)) { continue; }
        if (trim((string)$t) !== '') { $out[] = (string)$t; }
    }
    return $out;
}

/* ---------------------------------------------------------------------
   URLS
   --------------------------------------------------------------------- */
function hc_base()
{
    static $base = null;
    if ($base === null) {
        $base = rtrim((string)request()->getBaseUrl(), '/');
        if (substr($base, -10) === '/index.php') { $base = substr($base, 0, -10); }
    }
    return $base;
}

function hc_site_url()
{
    $u = trim((string)s('website_url', ''));
    if ($u !== '') { return rtrim($u, '/'); }
    return rtrim(request()->getSchemeAndHttpHost() . hc_base(), '/');
}

function hc_url($link = '')
{
    $link = trim((string)$link);
    if (preg_match('~^(https?:|mailto:|tel:|#|//)~i', $link)) { return $link; }
    $link = trim($link, '/');
    if ($link === 'home' || $link === 'index') { $link = ''; }
    return hc_base() . '/' . $link;
}

function hc_abs($link = '')
{
    $link = trim((string)$link, '/');
    if ($link === 'home') { $link = ''; }
    return hc_site_url() . '/' . $link;
}

function hc_img($path)
{
    $path = trim((string)$path);
    if ($path === '' || preg_match('~^(https?:)?//~i', $path)) { return $path; }
    return hc_base() . '/' . ltrim($path, '/');
}

function hc_abs_img($path)
{
    $path = trim((string)$path);
    if ($path === '' || preg_match('~^https?://~i', $path)) { return $path; }
    return hc_site_url() . '/' . ltrim($path, '/');
}

function hc_asset($path)
{
    $file = public_path($path);
    $v = is_file($file) ? filemtime($file) : '1';
    return hc_base() . '/' . $path . '?v=' . $v;
}

/* ---------------------------------------------------------------------
   PHONE / WHATSAPP
   --------------------------------------------------------------------- */
function hc_digits($v)
{
    return preg_replace('/\D+/', '', (string)$v);
}

function hc_tel()
{
    $d = hc_digits(s('mobile'));
    if (strlen($d) === 11 && $d[0] === '0') { $d = substr($d, 1); }
    if (strlen($d) === 10) { $d = '91' . $d; }
    return 'tel:+' . $d;
}

function hc_wa($item = '')
{
    $num = hc_digits(s('whatsapp_number'));
    if (strlen($num) === 10) { $num = '91' . $num; }
    $msg = ($item !== '' && $item !== null)
        ? str_replace('{item}', $item, (string)s('whatsapp_card_message', 'Hi, I want to know about {item}.'))
        : (string)s('whatsapp_message', 'Hi, I need more information.');
    return 'https://wa.me/' . $num . '?text=' . rawurlencode($msg);
}

/* ---------------------------------------------------------------------
   SERVICE CARDS (from Admin → Services)
   --------------------------------------------------------------------- */
function hc_cards($all = false)
{
    if (!isset($GLOBALS['HC']['cards'])) {
        $GLOBALS['HC']['cards'] = Service::query()->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn ($s) => $s->toCard())->all();
    }
    if ($all) { return $GLOBALS['HC']['cards']; }
    return array_values(array_filter($GLOBALS['HC']['cards'], function ($c) {
        return on($c['show']);
    }));
}

function hc_find_card($title)
{
    $title = trim(mb_strtolower((string)$title, 'UTF-8'));
    if ($title === '') { return null; }
    foreach (hc_cards() as $card) {
        if (trim(mb_strtolower($card['title'], 'UTF-8')) === $title) { return $card; }
    }
    return null;
}

function hc_inr($n)
{
    $n = (string)round((float)$n);
    $last3 = substr($n, -3);
    $rest = substr($n, 0, -3);
    if ($rest !== '' && $rest !== false) {
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        return $rest . ',' . $last3;
    }
    return $last3;
}

function hc_price($p)
{
    $p = trim((string)$p);
    if ($p === '') { return ''; }
    $clean = str_replace(array(',', ' '), '', $p);
    return is_numeric($clean) ? '₹' . hc_inr($clean) : $p;
}

function hc_card_details($card)
{
    $parts = array();
    if (!empty($card['badge'])) { $parts[] = $card['badge']; }
    if (isset($card['price']) && trim((string)$card['price']) !== '') { $parts[] = hc_price($card['price']); }
    return implode(' · ', $parts);
}

/* ---------------------------------------------------------------------
   TEXT HELPERS
   --------------------------------------------------------------------- */
// Paragraph text: an empty line = new paragraph. Simple tags allowed.
function hc_paras($text, $class = '')
{
    $text = trim(str_replace("\r", '', (string)$text));
    if ($text === '') { return ''; }
    $out = '';
    foreach (preg_split('/\n\s*\n/', $text) as $p) {
        $p = strip_tags(trim($p), '<b><strong><i><em><a><br><span><u>');
        $out .= '<p' . ($class ? ' class="' . $class . '"' : '') . '>' . nl2br($p, false) . '</p>';
    }
    return $out;
}

// Heading with [highlighted words] in brand colour.
function hc_title($text)
{
    $t = hc_e($text);
    return preg_replace('/\[(.+?)\]/', '<em>$1</em>', $t);
}

function hc_plain_title($text)
{
    return str_replace(array('[', ']'), '', (string)$text);
}

/** Rich HTML from the editor — keeps safe tags only. */
function hc_rich($html)
{
    $html = hc_fill((string)$html);
    $html = strip_tags($html, '<h2><h3><h4><p><ul><ol><li><b><strong><i><em><a><br><u><s><blockquote><img><figure><figcaption><table><thead><tbody><tr><th><td><hr><span><div>');
    // remove inline event handlers and javascript: links
    $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2/i', '$1="#"', $html);
    return $html;
}

/* ---------------------------------------------------------------------
   LINKS & BUTTONS
   'popup' = opens enquiry popup, '#section' = scroll, 'page-name' = page, '' = no link
   --------------------------------------------------------------------- */
function hc_button($text, $link, $class = 'btn btn-main', $item = '', $details = '')
{
    $link = trim((string)$link);
    if ($text === '' || $text === null) { return ''; }
    if ($link === 'popup') {
        return '<button type="button" class="' . $class . '" data-popup data-item="' . hc_e($item) . '" data-details="' . hc_e($details) . '">' . hc_e($text) . '</button>';
    }
    if ($link === '') { return ''; }
    return '<a class="' . $class . '" href="' . hc_e(hc_url($link)) . '">' . hc_e($text) . '</a>';
}

function hc_box_link($link, $item = '', $class = '')
{
    $link = trim((string)$link);
    if ($link === 'popup') {
        return array('<button type="button" class="' . $class . ' is-link" data-popup data-item="' . hc_e($item) . '">', '</button>');
    }
    if ($link !== '') {
        return array('<a class="' . $class . ' is-link" href="' . hc_e(hc_url($link)) . '">', '</a>');
    }
    return array('<div class="' . $class . '">', '</div>');
}

function hc_btn_style($type)
{
    $bg = s('buttons.' . $type . '_bg', '');
    $col = s('buttons.' . $type . '_color', '');
    $st = '';
    if ($bg !== '') { $st .= 'background:' . hc_e($bg) . ';'; }
    if ($col !== '') { $st .= 'color:' . hc_e($col) . ';'; }
    return $st ? ' style="' . $st . '"' : '';
}

function hc_call_btn($class = 'btn btn-call', $text = null)
{
    $text = $text === null ? s('buttons.call_text', 'Call Now') : $text;
    return '<a class="' . $class . '" href="' . hc_e(hc_tel()) . '" data-track="call">' . hc_icon('phone') . '<span>' . hc_e($text) . '</span></a>';
}

function hc_wa_btn($class = 'btn btn-wa', $item = '', $text = null)
{
    $text = $text === null ? s('buttons.whatsapp_text', 'WhatsApp') : $text;
    return '<a class="' . $class . '" href="' . hc_e(hc_wa($item)) . '" target="_blank" rel="noopener" data-track="whatsapp">' . hc_icon('whatsapp') . '<span>' . hc_e($text) . '</span></a>';
}

/* ---------------------------------------------------------------------
   FORMS (one field list for every form — Admin → Site Settings → Form)
   --------------------------------------------------------------------- */
function hc_secret()
{
    return hash('sha256', (string)config('app.key') . '|hc-form-key');
}

function hc_token()
{
    $t = time();
    return $t . '.' . substr(hash_hmac('sha256', (string)$t, hc_secret()), 0, 24);
}

function hc_form_fields()
{
    $fields = array();
    foreach (hc_list(s('form_fields', array())) as $f) {
        if (!is_array($f) || empty($f['name'])) { continue; }
        $f['name'] = preg_replace('/[^a-z0-9_]/i', '', $f['name']);
        if ($f['name'] === '') { continue; }
        $f['label'] = isset($f['label']) && $f['label'] !== '' ? $f['label'] : ucfirst($f['name']);
        $f['type'] = isset($f['type']) && $f['type'] ? strtolower($f['type']) : 'text';
        $f['required'] = isset($f['required']) && on($f['required']);
        $fields[] = $f;
    }
    if (!$fields) {
        $fields = array(
            array('name' => 'name', 'label' => 'Your Name', 'type' => 'text', 'required' => true),
            array('name' => 'mobile', 'label' => 'Mobile Number', 'type' => 'tel', 'required' => true),
        );
    }
    return $fields;
}

function hc_select_options($f)
{
    $opts = isset($f['options']) ? $f['options'] : array();
    if (is_string($opts)) {
        $t = trim($opts);
        if ($t === 'packages' || $t === 'cards' || $t === 'services') {
            $opts = array();
            foreach (hc_cards() as $card) { $opts[] = $card['title']; }
            $opts[] = 'Other / Not Decided';
        } else {
            $opts = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $t)), 'strlen'));
        }
    }
    return hc_list($opts);
}

function hc_form($id, $name, $short = false, $item = '', $submitText = null)
{
    static $n = 0;
    $n++;
    $submitText = $submitText === null || $submitText === '' ? s('buttons.submit_text', 'Submit') : $submitText;
    $h = '<form class="hc-form" id="' . hc_e($id) . '" action="' . hc_e(hc_url('send')) . '" method="post" novalidate>';
    $h .= '<input type="hidden" name="form_name" value="' . hc_e($name) . '">';
    $h .= '<input type="hidden" name="enquiry_item" value="' . hc_e($item) . '">';
    $h .= '<input type="hidden" name="page_url" value="">';
    $h .= '<input type="hidden" name="hc_ts" value="' . hc_e(hc_token()) . '">';
    $h .= '<div class="hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
    $h .= '<div class="fields">';
    foreach (hc_form_fields() as $f) {
        if ($short && isset($f['short_form']) && !on($f['short_form'])) { continue; }
        $fid = 'f' . $n . '_' . $f['name'];
        $req = $f['required'] ? ' required' : '';
        $ph = isset($f['placeholder']) && $f['placeholder'] !== '' ? ' placeholder="' . hc_e($f['placeholder']) . '"' : '';
        $wide = $f['type'] === 'textarea' || (isset($f['wide']) && on($f['wide'])) ? ' wide' : '';
        $h .= '<div class="field' . $wide . '"><label for="' . $fid . '">' . hc_e($f['label']) . ($f['required'] ? ' <i>*</i>' : '') . '</label>';
        switch ($f['type']) {
            case 'textarea':
                $h .= '<textarea id="' . $fid . '" name="' . $f['name'] . '" rows="3"' . $ph . $req . '></textarea>';
                break;
            case 'select':
                $h .= '<select id="' . $fid . '" name="' . $f['name'] . '"' . $req . ' data-select>';
                $h .= '<option value="">' . hc_e(isset($f['placeholder']) && $f['placeholder'] !== '' ? $f['placeholder'] : 'Select') . '</option>';
                foreach (hc_select_options($f) as $o) {
                    $sel = ($item !== '' && $o === $item) ? ' selected' : '';
                    $h .= '<option' . $sel . '>' . hc_e($o) . '</option>';
                }
                $h .= '</select>';
                break;
            case 'tel':
                $h .= '<input id="' . $fid . '" type="tel" name="' . $f['name'] . '" inputmode="numeric" maxlength="10" pattern="[6-9][0-9]{9}" autocomplete="tel-national" data-mobile' . $ph . $req . '>';
                $h .= '<small class="msg">Please enter a valid 10-digit mobile number (e.g. 9876543210).</small>';
                break;
            case 'email':
                $h .= '<input id="' . $fid . '" type="email" name="' . $f['name'] . '" autocomplete="email"' . $ph . $req . '>';
                $h .= '<small class="msg">Please enter a valid email address.</small>';
                break;
            case 'date':
                $h .= '<input id="' . $fid . '" type="date" name="' . $f['name'] . '" min="' . date('Y-m-d') . '"' . $req . '>';
                $h .= '<small class="msg">Please choose today or a future date.</small>';
                break;
            default:
                $auto = $f['name'] === 'name' ? ' autocomplete="name"' : '';
                $h .= '<input id="' . $fid . '" type="text" name="' . $f['name'] . '" maxlength="150"' . $auto . $ph . $req . '>';
        }
        if ($f['type'] !== 'tel' && $f['type'] !== 'email' && $f['type'] !== 'date') {
            $h .= '<small class="msg">This field is required.</small>';
        }
        $h .= '</div>';
    }
    $h .= '</div>';
    $h .= '<div class="form-error" role="alert" hidden></div>';
    $h .= '<button type="submit" class="btn btn-submit"' . hc_btn_style('submit') . ' data-sending="' . hc_e(s('buttons.sending_text', 'Sending…')) . '">' . hc_e($submitText) . '</button>';
    $note = s('privacy_note', '');
    if ($note !== '') { $h .= '<p class="privacy">' . hc_icon('lock') . ' ' . hc_e($note) . '</p>'; }
    $h .= '</form>';
    return $h;
}

/* ---------------------------------------------------------------------
   TRACKING CODES (Admin → Site Settings → Tracking)
   --------------------------------------------------------------------- */
function hc_tracking($box)
{
    if (!empty($GLOBALS['HC']['preview'])) { return ''; }
    $on = on(s('tracking.' . $box . '_code_on', false));
    $code = (string)s('tracking.' . $box . '_code', '');
    if (!$on || trim($code) === '') { return ''; }
    return "\n" . $code . "\n";
}

/* ---------------------------------------------------------------------
   ICONS (inline SVG)
   --------------------------------------------------------------------- */
function hc_icon($name)
{
    $p = array(
        'phone' => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/>',
        'whatsapp' => '<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-3 .78.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.25-.13-1.47-.73-1.7-.81-.23-.08-.4-.12-.56.12-.17.25-.64.81-.79.98-.14.17-.29.19-.54.06a6.7 6.7 0 0 1-3.3-2.9c-.25-.43.25-.4.71-1.33.08-.17.04-.31-.02-.43-.06-.13-.56-1.35-.77-1.85-.2-.48-.4-.42-.56-.42h-.48a.92.92 0 0 0-.67.31 2.8 2.8 0 0 0-.87 2.08 4.9 4.9 0 0 0 1.02 2.58 11.1 11.1 0 0 0 4.27 3.77c1.6.69 2.22.75 3.02.63.49-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/>',
        'mail' => '<path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5-8-5V6l8 5 8-5z"/>',
        'pin' => '<path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/>',
        'clock' => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 10.4 3.3 2-.8 1.3L11 13V7h2z"/>',
        'lock' => '<path d="M17 9V7A5 5 0 0 0 7 7v2a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2zm-8-2a3 3 0 0 1 6 0v2H9z"/>',
        'check' => '<path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/>',
        'star' => '<path d="m12 17.3 6.2 3.7-1.6-7 5.4-4.7-7.1-.6L12 2 9.1 8.7 2 9.3l5.4 4.7-1.6 7z"/>',
        'facebook' => '<path d="M14 9V7c0-.9.6-1 1-1h2V2h-3c-3.3 0-4 2.4-4 4v3H8v4h2v9h4v-9h3l.5-4z"/>',
        'instagram' => '<path d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4zM17.3 5.5a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4zM12 2c-2.7 0-3 0-4.1.1C4.5 2.2 2.3 4.4 2.1 7.9 2 9 2 9.3 2 12s0 3 .1 4.1c.2 3.4 2.4 5.6 5.8 5.8 1.1.1 1.4.1 4.1.1s3 0 4.1-.1c3.4-.2 5.6-2.4 5.8-5.8.1-1.1.1-1.4.1-4.1s0-3-.1-4.1c-.2-3.4-2.4-5.6-5.8-5.8C15 2 14.7 2 12 2zm0 1.8c2.7 0 3 0 4 .1 2.6.1 3.9 1.4 4 4 .1 1 .1 1.3.1 4s0 3-.1 4c-.1 2.6-1.4 3.9-4 4-1 .1-1.3.1-4 .1s-3 0-4-.1c-2.6-.1-3.9-1.4-4-4-.1-1-.1-1.3-.1-4s0-3 .1-4c.1-2.6 1.4-3.9 4-4 1-.1 1.3-.1 4-.1z"/>',
        'youtube' => '<path d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .5 12a31 31 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8zM9.8 15V9l5.8 3z"/>',
        'linkedin' => '<path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.5h4V21H3zM9.5 9.5h3.8v1.6h.06c.53-1 1.83-2.06 3.77-2.06 4.03 0 4.77 2.65 4.77 6.1V21h-4v-5.2c0-1.24-.02-2.84-1.73-2.84-1.73 0-2 1.35-2 2.75V21h-4z"/>',
        'x' => '<path d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.2-8.3L1.8 3h6.4l4.4 5.8zm-1.1 16.2h1.7L7.4 4.7H5.6z"/>',
        'arrow' => '<path d="M13.2 5.3 19.9 12l-6.7 6.7-1.4-1.4 4.3-4.3H4v-2h12.1l-4.3-4.3z"/>',
        'play' => '<path d="M8 5v14l11-7z"/>',
    );
    if (!isset($p[$name])) { return ''; }
    return '<svg class="ic" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor">' . $p[$name] . '</svg>';
}
