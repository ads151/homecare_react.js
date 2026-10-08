<?php
/* =====================================================================
   SHARED PARTS — cards grid, section headings and the Page Builder
   sections. All sections reuse the original website design classes.
   ===================================================================== */

function hc_card_html($card, $formItem = true)
{
    $title = $card['title'];
    $det = hc_card_details($card);
    $cat = isset($card['category']) ? trim((string)$card['category']) : '';
    $btnText = !empty($card['button_text']) ? $card['button_text'] : s('buttons.main_text', 'Book Now');
    $h = '<article class="card" data-cat="' . hc_e($cat) . '">';
    $h .= '<div class="ph">';
    if (!empty($card['image'])) {
        $h .= '<img src="' . hc_e(hc_img($card['image'])) . '" alt="' . hc_e(!empty($card['image_alt']) ? $card['image_alt'] : $title) . '" loading="lazy" decoding="async" width="700" height="440">';
    }
    if (!empty($card['badge'])) { $h .= '<span class="badge">' . hc_e($card['badge']) . '</span>'; }
    if (!empty($card['label'])) { $h .= '<span class="label">' . hc_e($card['label']) . '</span>'; }
    $h .= '</div><div class="body"><h3>' . hc_e($title) . '</h3>';
    if (!empty($card['text'])) { $h .= '<p class="ctext">' . hc_e($card['text']) . '</p>'; }
    $feat = hc_strings(isset($card['features']) ? $card['features'] : array());
    if ($feat) {
        $h .= '<ul class="ticks-sm">';
        foreach ($feat as $f) { $h .= '<li>' . hc_icon('check') . hc_e($f) . '</li>'; }
        $h .= '</ul>';
    }
    $price = isset($card['price']) ? hc_price($card['price']) : '';
    if ($price !== '') {
        $h .= '<div class="price">' . (!empty($card['price_prefix']) ? '<small>' . hc_e($card['price_prefix']) . '</small>' : '') . '<b>' . hc_e($price) . '</b>';
        if (!empty($card['old_price'])) { $h .= '<s>' . hc_e(hc_price($card['old_price'])) . '</s>'; }
        if (!empty($card['price_note'])) { $h .= '<small>' . hc_e($card['price_note']) . '</small>'; }
        $h .= '</div>';
    }
    $h .= '<div class="card-btns"><button type="button" class="btn btn-main" data-popup data-item="' . hc_e($title) . '" data-details="' . hc_e($det) . '">' . hc_e($btnText) . '</button>';
    if (on(s('buttons.card_show_call', true))) {
        $h .= '<a class="round round-call" href="' . hc_e(hc_tel()) . '" aria-label="Call about ' . hc_e($title) . '" data-track="call">' . hc_icon('phone') . '</a>';
    }
    if (on(s('buttons.card_show_whatsapp', true))) {
        $h .= '<a class="round round-wa" href="' . hc_e(hc_wa($title)) . '" target="_blank" rel="noopener" aria-label="WhatsApp about ' . hc_e($title) . '" data-track="whatsapp">' . hc_icon('whatsapp') . '</a>';
    }
    $h .= '</div></div></article>';
    return $h;
}

function hc_cards_grid($filter = false, $limit = 0, $category = '')
{
    $cards = hc_cards();
    if ($category !== '' && $category !== null) {
        $cards = array_values(array_filter($cards, function ($cd) use ($category) {
            return isset($cd['category']) && trim((string)$cd['category']) === trim((string)$category);
        }));
    }
    if ($limit > 0) { $cards = array_slice($cards, 0, $limit); }
    $cats = array();
    foreach ($cards as $cd) {
        if (!empty($cd['category']) && !in_array(trim($cd['category']), $cats, true)) { $cats[] = trim($cd['category']); }
    }
    $h = '';
    if ($filter && count($cats) >= 2) {
        $h .= '<div class="filters" role="group" aria-label="Filter"><button type="button" class="on" data-filter="">All</button>';
        foreach ($cats as $ct) { $h .= '<button type="button" data-filter="' . hc_e($ct) . '">' . hc_e($ct) . '</button>'; }
        $h .= '</div>';
    }
    $h .= '<div class="cards">';
    foreach ($cards as $cd) { $h .= hc_card_html($cd); }
    return $h . '</div>';
}

/** Section heading from explicit values. */
function hc_head_html($small, $heading, $text, $center = true)
{
    $small = hc_fill((string)$small);
    $heading = hc_fill((string)$heading);
    $text = hc_fill((string)$text);
    if ($small === '' && $heading === '' && trim($text) === '') { return ''; }
    $h = '<div class="sec-head' . ($center ? '' : ' left') . '">';
    if ($small !== '') { $h .= '<span class="eyebrow">' . hc_e($small) . '</span>'; }
    if ($heading !== '') { $h .= '<h2>' . hc_title($heading) . '</h2>'; }
    $h .= hc_paras($text);
    return $h . '</div>';
}

function hc_sec_head($n, $center = true)
{
    return hc_head_html(c('section' . $n . '_small', ''), c('section' . $n . '_heading', ''), c('section' . $n . '_text', ''), $center);
}

function hc_show($n)
{
    return on(c('section' . $n . '_show', true));
}

/* ---------------------------------------------------------------------
   Reusable section HTML (used by the fixed page designs AND the builder)
   --------------------------------------------------------------------- */
function hc_stats_html($items, $plain = true)
{
    $h = '';
    foreach (hc_list($items) as $st) {
        $val = hc_i($st, 'value', 0);
        if ($val === '') { continue; }
        $h .= '<div class="stat"><b>' . hc_e($val) . '</b><span>' . hc_e(hc_i($st, 'label', 1)) . '</span></div>';
    }
    if ($h === '') { return ''; }
    return '<section class="stats' . ($plain ? ' plain' : '') . '"><div class="wrap"><div class="stats-grid">' . $h . '</div></div></section>';
}

function hc_steps_html($items)
{
    $h = '<div class="steps">';
    foreach (hc_list($items) as $i => $st) {
        $h .= '<div class="step"><span class="num">' . ($i + 1) . '</span><span class="emo">' . hc_e(hc_i($st, 'icon', 0)) . '</span><h3>' . hc_e(hc_i($st, 'title', 1)) . '</h3><p>' . hc_e(hc_i($st, 'text', 2)) . '</p></div>';
    }
    return $h . '</div>';
}

function hc_feat_html($items)
{
    $h = '<div class="feat">';
    foreach (hc_list($items) as $f) {
        $h .= '<div><span class="fi">' . hc_e(hc_i($f, 'icon', 0)) . '</span><div><h3>' . hc_e(hc_i($f, 'title', 1)) . '</h3><p>' . hc_e(hc_i($f, 'text', 2)) . '</p></div></div>';
    }
    return $h . '</div>';
}

function hc_boxes_html($items)
{
    $h = '<div class="boxes3">';
    foreach (hc_list($items) as $b) {
        $h .= '<div class="box"><span class="fi big">' . hc_e(hc_i($b, 'icon', 0)) . '</span><h3>' . hc_e(hc_i($b, 'title', 1)) . '</h3>' . hc_paras(hc_i($b, 'text', 2)) . '</div>';
    }
    return $h . '</div>';
}

function hc_photo_cards_html($items)
{
    $h = '<div class="photo-cards">';
    foreach (hc_list($items) as $b) {
        $title = hc_i($b, 'title');
        $tag = hc_box_link(hc_i($b, 'link'), $title, 'pcard');
        $h .= $tag[0];
        if (hc_i($b, 'image') !== '') {
            $alt = hc_i($b, 'image_alt') !== '' ? hc_i($b, 'image_alt') : $title;
            $h .= '<img src="' . hc_e(hc_img(hc_i($b, 'image'))) . '" alt="' . hc_e($alt) . '" loading="lazy" decoding="async" width="700" height="440">';
        }
        $h .= '<span class="pc-txt"><b>' . hc_e($title) . '</b>' . (hc_i($b, 'text') !== '' ? '<small>' . hc_e(hc_i($b, 'text')) . '</small>' : '') . '</span>';
        $h .= $tag[1];
    }
    return $h . '</div>';
}

function hc_ticks_html($items, $class = 'ticks dark')
{
    $list = hc_strings($items);
    if (!$list) { return ''; }
    $h = '<ul class="' . $class . '">';
    foreach ($list as $p) { $h .= '<li>' . hc_icon('check') . hc_e(hc_fill($p)) . '</li>'; }
    return $h . '</ul>';
}

function hc_areas_html($items)
{
    $h = '<div class="areas">';
    foreach (hc_strings($items) as $a) { $h .= '<span>' . hc_icon('pin') . hc_e($a) . '</span>'; }
    return $h . '</div>';
}

function hc_testimonials_html($items)
{
    $h = '<div class="tgrid">';
    foreach (hc_list($items) as $t) {
        $name = hc_i($t, 'name', 0);
        if ($name === '') { continue; }
        $words = preg_split('/\s+/', trim($name));
        $ini = '';
        foreach (array_slice($words, 0, 2) as $w) { $ini .= mb_substr($w, 0, 1, 'UTF-8'); }
        $h .= '<figure class="t"><span class="stars">' . str_repeat(hc_icon('star'), 5) . '</span><blockquote>' . hc_e(hc_i($t, 'text', 2)) . '</blockquote>'
            . '<figcaption><span class="av">' . hc_e(mb_strtoupper($ini, 'UTF-8')) . '</span><span><b>' . hc_e($name) . '</b><small>' . hc_e(hc_i($t, 'area', 1)) . '</small></span></figcaption></figure>';
    }
    return $h . '</div>';
}

function hc_faq_html($items)
{
    $h = '<div class="faq">';
    $i = 0;
    foreach (hc_list($items) as $f) {
        $q = hc_i($f, 'question', 0);
        if ($q === '') { continue; }
        $h .= '<details' . ($i === 0 ? ' open' : '') . '><summary>' . hc_e($q) . '</summary><div>' . hc_paras(hc_i($f, 'answer', 1)) . '</div></details>';
        $i++;
    }
    return $h . '</div>';
}

function hc_contact_cards_html()
{
    return '<div class="ccards">'
        . '<a class="ccard" href="' . hc_e(hc_tel()) . '" data-track="call"><span class="ci">' . hc_icon('phone') . '</span><small>Call Us</small><b>' . hc_e(s('mobile')) . '</b></a>'
        . '<a class="ccard" href="' . hc_e(hc_wa()) . '" target="_blank" rel="noopener" data-track="whatsapp"><span class="ci wa">' . hc_icon('whatsapp') . '</span><small>WhatsApp</small><b>' . hc_e(s('mobile')) . '</b></a>'
        . '<a class="ccard" href="mailto:' . hc_e(s('email')) . '"><span class="ci">' . hc_icon('mail') . '</span><small>Email</small><b>' . hc_e(s('email')) . '</b></a>'
        . '<div class="ccard"><span class="ci">' . hc_icon('pin') . '</span><small>Address</small><b>' . hc_e(s('address')) . '</b></div>'
        . '<div class="ccard"><span class="ci">' . hc_icon('clock') . '</span><small>Working Hours</small><b>' . hc_e(s('working_hours')) . '</b></div>'
        . '</div>';
}

function hc_map_html($embed)
{
    $embed = trim((string)$embed);
    if (preg_match('~src="([^"]+)"~i', $embed, $m)) { $embed = html_entity_decode($m[1]); }
    if ($embed === '' || !preg_match('~^https://~i', $embed)) { return ''; }
    return '<div class="map"><iframe src="' . hc_e($embed) . '" title="Map – ' . hc_e(s('business_name')) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>';
}

function hc_youtube_id($url)
{
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{6,})~', (string)$url, $m)) { return $m[1]; }
    return '';
}

/* ---------------------------------------------------------------------
   PAGE BUILDER SECTIONS
   Each block = ['type' => '…', 'data' => […]]
   --------------------------------------------------------------------- */
function hc_blocks($blocks)
{
    $out = '';
    foreach (hc_list($blocks) as $i => $b) {
        if (!is_array($b) || empty($b['type'])) { continue; }
        $d = isset($b['data']) && is_array($b['data']) ? $b['data'] : array();
        if (isset($d['show']) && !on($d['show'])) { continue; }
        $fn = 'hc_block_' . preg_replace('/[^a-z0-9_]/', '', $b['type']);
        if (function_exists($fn)) { $out .= $fn($d, $i); }
    }
    return $out;
}

function hc_bd($d, $k, $def = '')
{
    return isset($d[$k]) && $d[$k] !== null ? hc_fill($d[$k]) : $def;
}

function hc_sec_open($d, $extraClass = '', $id = '')
{
    $bg = hc_bd($d, 'background', 'white');
    $cls = 'sec' . ($bg === 'light' ? ' bg-light' : '') . ($extraClass ? ' ' . $extraClass : '');
    $anchor = trim((string)hc_bd($d, 'anchor', $id));
    return '<section class="' . $cls . '"' . ($anchor !== '' ? ' id="' . hc_e(preg_replace('/[^a-z0-9_-]/i', '', $anchor)) . '"' : '') . '>';
}

function hc_d_head($d, $center = true)
{
    return hc_head_html(hc_bd($d, 'small'), hc_bd($d, 'heading'), hc_bd($d, 'text'), $center);
}

function hc_block_heading_text($d)
{
    $center = hc_bd($d, 'align', 'center') !== 'left';
    $h = hc_sec_open($d) . '<div class="wrap' . ($center ? '' : ' narrow') . '">' . hc_d_head($d, $center);
    if (hc_bd($d, 'button_text') !== '') {
        $h .= '<div class="' . ($center ? 'center ' : '') . 'mt">' . hc_button(hc_bd($d, 'button_text'), hc_bd($d, 'button_link', 'popup'), 'btn btn-main') . '</div>';
    }
    return $h . '</div></section>';
}

function hc_block_rich_text($d)
{
    $h = hc_sec_open($d) . '<div class="wrap narrow legal">';
    if (hc_bd($d, 'heading') !== '') { $h .= hc_head_html(hc_bd($d, 'small'), hc_bd($d, 'heading'), '', false); }
    $h .= hc_rich(hc_bd($d, 'body'));
    return $h . '</div></section>';
}

function hc_block_image_text($d)
{
    $img = hc_bd($d, 'image');
    $alt = hc_bd($d, 'image_alt') !== '' ? hc_bd($d, 'image_alt') : hc_plain_title(hc_bd($d, 'heading'));
    $imgHtml = '<div class="about-img">' . ($img !== '' ? '<img src="' . hc_e(hc_img($img)) . '" alt="' . hc_e($alt) . '" loading="lazy" decoding="async" width="900" height="700">' : '') . '</div>';
    $txt = '<div>' . hc_d_head($d, false) . hc_ticks_html(hc_bd($d, 'points', array()));
    if (hc_bd($d, 'button_text') !== '') { $txt .= '<div class="mt">' . hc_button(hc_bd($d, 'button_text'), hc_bd($d, 'button_link', 'popup'), 'btn btn-main') . '</div>'; }
    $txt .= '</div>';
    $right = hc_bd($d, 'image_position', 'left') === 'right';
    return hc_sec_open($d) . '<div class="wrap two about-intro' . ($right ? ' img-right' : '') . '">' . ($right ? $txt . $imgHtml : $imgHtml . $txt) . '</div></section>';
}

function hc_block_stats($d)
{
    return hc_stats_html(hc_bd($d, 'items', array()), true);
}

function hc_block_icon_boxes($d)
{
    return hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d) . hc_boxes_html(hc_bd($d, 'items', array())) . '</div></section>';
}

function hc_block_steps($d)
{
    return hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d) . hc_steps_html(hc_bd($d, 'items', array())) . '</div></section>';
}

function hc_block_why($d)
{
    $img = hc_bd($d, 'image');
    $alt = hc_bd($d, 'image_alt') !== '' ? hc_bd($d, 'image_alt') : hc_plain_title(hc_bd($d, 'heading'));
    $h = hc_sec_open($d, 'why') . '<div class="wrap two"><div class="why-img">';
    if ($img !== '') { $h .= '<img src="' . hc_e(hc_img($img)) . '" alt="' . hc_e($alt) . '" loading="lazy" decoding="async" width="800" height="860">'; }
    if (hc_bd($d, 'badge_big') !== '') { $h .= '<div class="float"><b>' . hc_e(hc_bd($d, 'badge_big')) . '</b><span>' . hc_e(hc_bd($d, 'badge_text')) . '</span></div>'; }
    $h .= '</div><div>' . hc_d_head($d, false) . hc_feat_html(hc_bd($d, 'items', array()));
    if (hc_bd($d, 'button_text') !== '') { $h .= '<div class="mt">' . hc_button(hc_bd($d, 'button_text'), hc_bd($d, 'button_link', 'popup'), 'btn btn-main') . '</div>'; }
    return $h . '</div></div></section>';
}

function hc_block_services($d)
{
    $h = hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d) . hc_cards_grid(on(hc_bd($d, 'filter', false)), (int)hc_bd($d, 'limit', 0), hc_bd($d, 'category', ''));
    if (hc_bd($d, 'button_text') !== '') { $h .= '<div class="center mt">' . hc_button(hc_bd($d, 'button_text'), hc_bd($d, 'button_link', ''), 'btn btn-outline') . '</div>'; }
    return $h . '</div></section>';
}

function hc_block_photo_cards($d)
{
    return hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d) . hc_photo_cards_html(hc_bd($d, 'items', array())) . '</div></section>';
}

function hc_block_faq($d)
{
    return hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d) . hc_faq_html(hc_bd($d, 'items', array())) . '</div></section>';
}

function hc_block_testimonials($d)
{
    return hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d) . hc_testimonials_html(hc_bd($d, 'items', array())) . '</div></section>';
}

function hc_block_areas($d)
{
    return hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d) . hc_areas_html(hc_bd($d, 'items', array())) . '</div></section>';
}

function hc_block_cta($d)
{
    $heading = hc_bd($d, 'heading') !== '' ? hc_bd($d, 'heading') : s('cta_heading', '');
    $text = hc_bd($d, 'text') !== '' ? hc_bd($d, 'text') : s('cta_text', '');
    return hc_cta_band($heading, $text);
}

function hc_block_form($d, $i = 0)
{
    $h = '<section class="sec final"' . (hc_bd($d, 'anchor') !== '' ? ' id="' . hc_e(preg_replace('/[^a-z0-9_-]/i', '', hc_bd($d, 'anchor'))) . '"' : '') . '><div class="wrap two"><div>';
    if (hc_bd($d, 'small') !== '') { $h .= '<span class="eyebrow light">' . hc_e(hc_bd($d, 'small')) . '</span>'; }
    if (hc_bd($d, 'heading') !== '') { $h .= '<h2>' . hc_title(hc_bd($d, 'heading')) . '</h2>'; }
    $h .= hc_paras(hc_bd($d, 'text'));
    if (on(hc_bd($d, 'show_contact', true))) {
        $h .= '<ul class="contact-list">'
            . '<li><span class="ci">' . hc_icon('phone') . '</span><span><small>Call / WhatsApp</small><a href="' . hc_e(hc_tel()) . '" data-track="call">' . hc_e(s('mobile')) . '</a></span></li>'
            . '<li><span class="ci">' . hc_icon('mail') . '</span><span><small>Email</small><a href="mailto:' . hc_e(s('email')) . '">' . hc_e(s('email')) . '</a></span></li>'
            . '<li><span class="ci">' . hc_icon('pin') . '</span><span><small>Address</small><b>' . hc_e(s('address')) . '</b></span></li>'
            . '</ul>';
    }
    $h .= '</div><div class="form-card"><h2>' . hc_e(hc_bd($d, 'form_heading', 'Request a Call Back')) . '</h2>';
    if (hc_bd($d, 'form_text') !== '') { $h .= '<p class="sub">' . hc_e(hc_bd($d, 'form_text')) . '</p>'; }
    $page = $GLOBALS['HC']['page'];
    $h .= hc_form('blockForm' . $i, ucfirst(str_replace('-', ' ', $page)) . ' Page Form', on(hc_bd($d, 'short_form', false)), hc_bd($d, 'enquiry_item'));
    return $h . '</div></div></section>';
}

function hc_block_contact($d)
{
    $h = hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d) . hc_contact_cards_html();
    $map = on(hc_bd($d, 'map_show', true)) ? hc_map_html(hc_bd($d, 'map_embed')) : '';
    if (on(hc_bd($d, 'form_show', true)) || $map !== '') {
        $h .= '<div class="contact-main two">';
        if (on(hc_bd($d, 'form_show', true))) {
            $h .= '<div class="form-card"><h2>' . hc_e(hc_bd($d, 'form_heading', 'Send Us a Message')) . '</h2>';
            if (hc_bd($d, 'form_text') !== '') { $h .= '<p class="sub">' . hc_e(hc_bd($d, 'form_text')) . '</p>'; }
            $h .= hc_form('contactBlockForm', 'Contact Form') . '</div>';
        }
        $h .= $map . '</div>';
    }
    return $h . '</div></section>';
}

function hc_block_map($d)
{
    $map = hc_map_html(hc_bd($d, 'map_embed'));
    if ($map === '') { return ''; }
    return hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d) . '<div class="map-full">' . $map . '</div></div></section>';
}

function hc_block_custom_box($d)
{
    $item = hc_bd($d, 'enquiry_item');
    return '<section class="sec sec-tight"><div class="wrap"><div class="custom-box">'
        . '<div><h2>' . hc_e(hc_bd($d, 'heading')) . '</h2>' . hc_paras(hc_bd($d, 'text')) . '</div>'
        . '<div class="cta-btns">' . hc_button(hc_bd($d, 'button_text', 'Enquire Now'), hc_bd($d, 'button_link', 'popup'), 'btn btn-main', $item) . hc_wa_btn('btn btn-wa', $item) . '</div>'
        . '</div></div></section>';
}

function hc_block_gallery($d)
{
    $h = hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d) . '<div class="hc-gallery">';
    foreach (hc_list(hc_bd($d, 'images', array())) as $img) {
        $path = is_array($img) ? hc_i($img, 'image') : $img;
        if ($path === '') { continue; }
        $alt = is_array($img) ? hc_i($img, 'alt') : '';
        $cap = is_array($img) ? hc_i($img, 'caption') : '';
        $h .= '<figure><img src="' . hc_e(hc_img($path)) . '" alt="' . hc_e($alt !== '' ? $alt : $cap) . '" loading="lazy" decoding="async">' . ($cap !== '' ? '<figcaption>' . hc_e($cap) . '</figcaption>' : '') . '</figure>';
    }
    return $h . '</div></div></section>';
}

function hc_block_video($d)
{
    $id = hc_youtube_id(hc_bd($d, 'url'));
    if ($id === '') { return ''; }
    return hc_sec_open($d) . '<div class="wrap">' . hc_d_head($d)
        . '<div class="hc-video"><iframe src="https://www.youtube-nocookie.com/embed/' . hc_e($id) . '" title="' . hc_e(hc_plain_title(hc_bd($d, 'heading', 'Video'))) . '" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>'
        . '</div></section>';
}

function hc_block_html($d)
{
    $code = (string)hc_bd($d, 'code');
    if (on(hc_bd($d, 'wrap', true))) {
        return hc_sec_open($d) . '<div class="wrap">' . $code . '</div></section>';
    }
    return $code;
}

/** Collect FAQ questions on the page (for Google FAQ rich results). */
function hc_collect_faq($content)
{
    $out = array();
    if (($content['template'] ?? '') === 'home' && on($content['section8_show'] ?? true)) {
        foreach (hc_list($content['section8_items'] ?? array()) as $f) {
            if (hc_i($f, 'question', 0) !== '') { $out[] = array(hc_fill(hc_i($f, 'question', 0)), hc_fill(hc_i($f, 'answer', 1))); }
        }
    }
    foreach (array('blocks', 'extra_blocks') as $key) {
        foreach (hc_list($content[$key] ?? array()) as $b) {
            if (!is_array($b) || ($b['type'] ?? '') !== 'faq') { continue; }
            if (isset($b['data']['show']) && !on($b['data']['show'])) { continue; }
            foreach (hc_list($b['data']['items'] ?? array()) as $f) {
                if (hc_i($f, 'question', 0) !== '') { $out[] = array(hc_fill(hc_i($f, 'question', 0)), hc_fill(hc_i($f, 'answer', 1))); }
            }
        }
    }
    return $out;
}
