<?php
/* =====================================================================
   SHARED LAYOUT — header, inner-page hero, CTA banner, footer,
   floating buttons, popup. Text comes from Admin → Site Settings.
   ===================================================================== */

function hc_page_title()
{
    $name = s('business_name', 'Home Care');
    if (c('seo_title', '') !== '') { return c('seo_title'); }
    $h = hc_plain_title(c('page_name', '') !== '' ? c('page_name') : c('hero_title', ''));
    return $h !== '' ? $h . ' | ' . $name : $name;
}

function hc_head()
{
    $page = $GLOBALS['HC']['page'];
    $name = s('business_name', 'Home Care');
    $title = hc_page_title();
    $desc = c('seo_description', '');
    $canon = c('canonical_url', '') !== '' ? c('canonical_url') : ($page === 'home' ? hc_abs('') : hc_abs($page));
    $ogTitle = c('og_title', '') !== '' ? c('og_title') : $title;
    $ogDesc = c('og_description', '') !== '' ? c('og_description') : $desc;
    $ogImg = c('og_image', '');
    if ($ogImg === '') { $ogImg = c('hero_image', ''); }
    if ($ogImg === '') { $ogImg = s('default_og_image', ''); }
    if ($ogImg === '') { $ogImg = s('logo', ''); }
    $index = on(c('robots_index', true)) && !on(c('noindex', false));
    $follow = on(c('robots_follow', true));
    $col = s('colors', array());
    $btn = s('buttons', array());
    $v = function ($arr, $k, $d) { return is_array($arr) && isset($arr[$k]) && $arr[$k] !== '' ? $arr[$k] : $d; };

    echo '<!doctype html><html lang="en-IN"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . hc_e($title) . '</title>';
    if ($desc !== '') { echo '<meta name="description" content="' . hc_e($desc) . '">'; }
    echo '<meta name="robots" content="' . ($index ? 'index' : 'noindex') . ', ' . ($follow ? 'follow' : 'nofollow') . ($index ? ', max-image-preview:large' : '') . '">';
    if ($index) { echo '<link rel="canonical" href="' . hc_e($canon) . '">'; }
    if (s('google_site_verification', '') !== '') { echo '<meta name="google-site-verification" content="' . hc_e(s('google_site_verification')) . '">'; }
    if (s('bing_site_verification', '') !== '') { echo '<meta name="msvalidate.01" content="' . hc_e(s('bing_site_verification')) . '">'; }
    echo '<meta name="theme-color" content="' . hc_e($v($col, 'primary', '#1f5c99')) . '">';
    echo '<meta property="og:type" content="website"><meta property="og:locale" content="en_IN"><meta property="og:site_name" content="' . hc_e($name) . '">';
    echo '<meta property="og:title" content="' . hc_e($ogTitle) . '"><meta property="og:url" content="' . hc_e($canon) . '">';
    if ($ogDesc !== '') { echo '<meta property="og:description" content="' . hc_e($ogDesc) . '">'; }
    if ($ogImg) { echo '<meta property="og:image" content="' . hc_e(hc_abs_img($ogImg)) . '">'; }
    echo '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="' . hc_e($ogTitle) . '">';
    if ($ogDesc !== '') { echo '<meta name="twitter:description" content="' . hc_e($ogDesc) . '">'; }
    if ($ogImg) { echo '<meta name="twitter:image" content="' . hc_e(hc_abs_img($ogImg)) . '">'; }
    echo '<link rel="icon" type="image/png" href="' . hc_e(hc_img(s('favicon', 'images/general/favicon.png'))) . '">';
    echo '<link rel="apple-touch-icon" href="' . hc_e(hc_img(s('apple_touch_icon', 'images/general/apple-touch-icon.png'))) . '">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">';

    // Hero preload (only the photo that is needed downloads)
    $hi = c('hero_image', '');
    $hm = c('hero_image_mobile', '');
    if ($hi !== '') {
        if ($hm !== '') {
            echo '<link rel="preload" as="image" href="' . hc_e(hc_img($hm)) . '" media="(max-width: 640px)" fetchpriority="high">';
            echo '<link rel="preload" as="image" href="' . hc_e(hc_img($hi)) . '" media="(min-width: 641px)" fetchpriority="high">';
        } else {
            echo '<link rel="preload" as="image" href="' . hc_e(hc_img($hi)) . '" fetchpriority="high">';
        }
    }
    echo '<link rel="stylesheet" href="' . hc_e(hc_asset('assets/site/style.css')) . '">';

    // Brand colours + button colours
    echo '<style>:root{'
        . '--primary:' . hc_e($v($col, 'primary', '#1f5c99')) . ';'
        . '--secondary:' . hc_e($v($col, 'secondary', '#1b8a6b')) . ';'
        . '--dark:' . hc_e($v($col, 'dark', '#0f2a52')) . ';'
        . '--accent:' . hc_e($v($col, 'accent', '#7cb93a')) . ';'
        . '--light:' . hc_e($v($col, 'light', '#eef6f6')) . ';'
        . '--main-bg:' . hc_e($v($btn, 'main_bg', '#2c9a55')) . ';--main-color:' . hc_e($v($btn, 'main_color', '#fff')) . ';'
        . '--call-bg:' . hc_e($v($btn, 'call_bg', '#1f5c99')) . ';--call-color:' . hc_e($v($btn, 'call_color', '#fff')) . ';'
        . '--wa-bg:' . hc_e($v($btn, 'whatsapp_bg', '#25D366')) . ';--wa-color:' . hc_e($v($btn, 'whatsapp_color', '#fff')) . ';'
        . '--submit-bg:' . hc_e($v($btn, 'submit_bg', '#2c9a55')) . ';--submit-color:' . hc_e($v($btn, 'submit_color', '#fff')) . ';'
        . '--quote-bg:' . hc_e($v($btn, 'quote_bg', '#ffffff')) . ';--quote-color:' . hc_e($v($btn, 'quote_color', '#0f2a52')) . ';'
        . '}</style>';

    hc_schema();
    if (c('head_code', '') !== '' && empty($GLOBALS['HC']['preview'])) { echo "\n" . c('head_code') . "\n"; }
    echo hc_tracking('head');
    if ($page === 'thank-you') { echo hc_tracking('thankyou'); }
    echo '</head>';
}

function hc_json_ld($data)
{
    echo '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

/** Structured data (JSON-LD) for the current page, as a list of arrays. */
function hc_schema_data()
{
    $out = array();
    $page = $GLOBALS['HC']['page'];
    $type = c('schema_type', '') !== '' ? c('schema_type') : ($page === 'home' ? s('schema_type', 'MedicalBusiness') : 'WebPage');

    // Business details (home page, or any page set to a business type)
    if ($page === 'home' || !in_array($type, array('WebPage', 'AboutPage', 'ContactPage', 'Article', 'Service', 'FAQPage', 'none'), true)) {
        $data = array(
            '@context' => 'https://schema.org',
            '@type' => $page === 'home' ? s('schema_type', 'MedicalBusiness') : $type,
            'name' => s('business_name'),
            'description' => c('seo_description', ''),
            'url' => hc_abs(''),
            'logo' => hc_abs_img(s('logo')),
            'image' => hc_abs_img(c('hero_image', '') !== '' ? c('hero_image') : s('logo')),
            'telephone' => str_replace('tel:', '', hc_tel()),
            'email' => s('email'),
            'address' => array(
                '@type' => 'PostalAddress',
                'streetAddress' => s('address'),
                'addressLocality' => s('city', ''),
                'addressRegion' => s('state', ''),
                'postalCode' => s('pincode', ''),
                'addressCountry' => 'IN',
            ),
            'openingHours' => s('schema_opening_hours', 'Mo-Su 00:00-23:59'),
            'priceRange' => s('price_range', '₹₹'),
        );
        $areas = hc_strings(c('section6_areas', array()));
        if ($areas) { $data['areaServed'] = $areas; }
        $same = array_values(array_filter(hc_list(s('social', array()))));
        if ($same) { $data['sameAs'] = $same; }
        $out[] = ($data);
    } elseif ($type !== 'none') {
        $data = array(
            '@context' => 'https://schema.org',
            '@type' => $type,
            'name' => hc_page_title(),
            'url' => hc_abs($page),
            'description' => c('seo_description', ''),
        );
        if ($type === 'Service') {
            $data['provider'] = array('@type' => s('schema_type', 'MedicalBusiness'), 'name' => s('business_name'), 'telephone' => str_replace('tel:', '', hc_tel()));
            $data['areaServed'] = s('city', '');
        }
        $out[] = ($data);
    }

    // Breadcrumb (inner pages)
    if ($page !== 'home' && $page !== '404') {
        $out[] = (array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array(
                array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => hc_abs('')),
                array('@type' => 'ListItem', 'position' => 2, 'name' => hc_plain_title(c('page_name', '') !== '' ? c('page_name') : c('hero_title', '')), 'item' => hc_abs($page)),
            ),
        ));
    }

    // FAQ (from any FAQ section on the page)
    $faq = isset($GLOBALS['HC']['faq']) ? $GLOBALS['HC']['faq'] : array();
    if ($faq) {
        $q = array();
        foreach ($faq as $f) {
            $q[] = array('@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => strip_tags($f[1])));
        }
        $out[] = (array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $q));
    }
    return $out;
}

function hc_schema()
{
    foreach (hc_schema_data() as $data) { hc_json_ld($data); }
}

function hc_logo($class = 'logo')
{
    $name = s('business_name', 'Home Care');
    $h = '<a href="' . hc_e(hc_url('')) . '" class="' . $class . '" aria-label="' . hc_e($name) . ' home">';
    if (s('logo', '') !== '') {
        $h .= '<img src="' . hc_e(hc_img(s('logo'))) . '" alt="' . hc_e($name) . ' logo" width="120" height="120">';
    }
    if (on(s('show_name_with_logo', true))) {
        $h .= '<span><b>' . hc_e($name) . '</b>';
        if (s('tagline', '') !== '') { $h .= '<small>' . hc_e(s('tagline')) . '</small>'; }
        $h .= '</span>';
    }
    return $h . '</a>';
}

function hc_menu($class = '')
{
    $page = $GLOBALS['HC']['page'];
    $h = '<ul class="' . $class . '">';
    foreach (hc_list(s('menu', array())) as $m) {
        $label = hc_i($m, 'label', 0);
        if ($label === '') { continue; }
        $link = trim((string)hc_i($m, 'link', 1), '/');
        $isActive = ($link === '' || $link === 'home') ? $page === 'home' : $link === $page;
        $h .= '<li><a href="' . hc_e(hc_url($link)) . '"' . ($isActive ? ' class="active" aria-current="page"' : '') . '>' . hc_e($label) . '</a></li>';
    }
    return $h . '</ul>';
}

function hc_layout_start()
{
    hc_head();
    $page = $GLOBALS['HC']['page'];
    $mainText = s('header_button_text', s('buttons.main_text', 'Book Now'));
    echo '<body class="page-' . hc_e($page) . '">';
    echo hc_tracking('bodystart');
    echo '<a class="skip" href="#main">Skip to content</a>';

    if (on(s('topbar_show', true))) {
        echo '<div class="topbar"><div class="wrap"><span class="live"><i class="dot"></i> ' . hc_e(s('topbar_text', '')) . '</span>'
            . '<span class="tb-right"><a href="mailto:' . hc_e(s('email')) . '">' . hc_icon('mail') . hc_e(s('email')) . '</a>'
            . '<span>' . hc_icon('clock') . hc_e(s('working_hours')) . '</span></span></div></div>';
    }

    echo '<header class="site-header"><div class="wrap">';
    echo hc_logo();
    echo '<nav class="main-nav" aria-label="Main menu">' . hc_menu() . '</nav>';
    echo '<div class="h-actions">';
    if (on(s('header_phone_show', true))) {
        echo '<a class="h-phone" href="' . hc_e(hc_tel()) . '" data-track="call"><span class="h-ic">' . hc_icon('phone') . '</span><span class="h-txt"><small>' . hc_e(s('header_phone_label', 'Call 24×7')) . '</small>' . hc_e(s('mobile')) . '</span></a>';
    }
    if (on(s('header_button_show', true))) {
        echo '<button type="button" class="btn btn-main h-btn" data-popup data-item="">' . hc_e($mainText) . '</button>';
    }
    echo '<button type="button" class="burger" aria-label="Open menu" aria-expanded="false" aria-controls="mnav"><span></span><span></span><span></span></button>';
    echo '</div></div></header>';

    // Mobile slide-in menu
    echo '<div class="mnav-overlay" data-close-menu></div>';
    echo '<aside class="mnav" id="mnav" aria-label="Mobile menu" aria-hidden="true"><div class="mnav-top">' . hc_logo('logo logo-sm') . '<button type="button" class="mnav-x" aria-label="Close menu" data-close-menu>×</button></div>';
    echo '<nav>' . hc_menu() . '</nav><div class="mnav-btns">' . hc_call_btn('btn btn-call', s('buttons.call_text', 'Call Now') . ' ' . s('mobile')) . hc_wa_btn() . '<button type="button" class="btn btn-main" data-popup data-item="">' . hc_e($mainText) . '</button></div></aside>';

    echo '<main id="main">';
    if (c('template', '') !== 'home' && on(c('hero_show', true))) { hc_inner_hero(); }
}

function hc_hero_picture($img, $mob, $alt)
{
    if ($img === '') { return ''; }
    $h = '<picture class="hero-bg">';
    if ($mob !== '') { $h .= '<source media="(max-width: 640px)" srcset="' . hc_e(hc_img($mob)) . '">'; }
    $h .= '<img src="' . hc_e(hc_img($img)) . '" alt="' . hc_e($alt) . '" fetchpriority="high" decoding="async"></picture>';
    return $h;
}

function hc_inner_hero()
{
    $name = c('page_name', '') !== '' ? c('page_name') : hc_plain_title(c('hero_title', ''));
    $alt = c('hero_image_alt', '') !== '' ? c('hero_image_alt') : $name;
    echo '<section class="page-hero">' . hc_hero_picture(c('hero_image', 'images/pages/page-hero.jpg'), c('hero_image_mobile', ''), $alt) . '<div class="wrap">';
    echo '<nav class="crumbs" aria-label="Breadcrumb"><a href="' . hc_e(hc_url('')) . '">Home</a> <span>›</span> <span aria-current="page">' . hc_e($name) . '</span></nav>';
    echo '<h1>' . hc_title(c('hero_title', '') !== '' ? c('hero_title') : $name) . '</h1>';
    if (c('hero_text', '') !== '') { echo '<p>' . hc_e(c('hero_text')) . '</p>'; }
    echo '</div></section>';
}

function hc_cta_band($heading, $text)
{
    $h = '<section class="cta-wrap"><div class="wrap"><div class="cta-band">';
    $img = s('cta_image', '');
    if ($img !== '') { $h .= '<img class="cta-bg" src="' . hc_e(hc_img($img)) . '" alt="" loading="lazy">'; }
    $h .= '<div class="cta-txt"><h2>' . hc_e($heading) . '</h2>' . ($text !== '' ? '<p>' . hc_e($text) . '</p>' : '') . '</div>';
    $h .= '<div class="cta-btns"><button type="button" class="btn btn-quote" data-popup data-item="">' . hc_e(s('buttons.quote_text', 'Get Free Quote')) . '</button>'
        . hc_call_btn('btn btn-call', s('mobile')) . '</div>';
    return $h . '</div></div></section>';
}

function hc_cta_banner()
{
    $heading = c('cta_heading', '') !== '' ? c('cta_heading') : s('cta_heading', 'Need care at home today?');
    $text = c('cta_text', '') !== '' ? c('cta_text') : s('cta_text', '');
    echo hc_cta_band($heading, $text);
}

function hc_layout_end($showCta = true)
{
    // Extra sections added from Admin → Pages → "Extra Sections"
    echo hc_blocks(c('extra_blocks', array()));

    if ($showCta) { hc_cta_banner(); }
    echo '</main>';

    // Footer
    echo '<footer class="site-footer"><div class="wrap"><div class="fgrid">';
    echo '<div class="f-about">' . hc_logo('logo logo-footer') . hc_paras(s('footer_about', ''));
    $soc = '';
    foreach ((is_array(s('social', array())) ? s('social', array()) : array()) as $net => $url) {
        if (trim((string)$url) === '') { continue; }
        $soc .= '<a href="' . hc_e($url) . '" target="_blank" rel="noopener" aria-label="' . hc_e(ucfirst($net)) . '">' . hc_icon($net) . '</a>';
    }
    if ($soc !== '') { echo '<div class="social">' . $soc . '</div>'; }
    echo '</div>';
    foreach (array(array(s('footer_quick_title', 'Quick Links'), s('footer_quick_links', array())), array(s('footer_info_title', 'Information'), s('footer_info_links', array()))) as $col) {
        echo '<div><h3 class="f-h">' . hc_e($col[0]) . '</h3><ul>';
        foreach (hc_list($col[1]) as $l) {
            $label = hc_i($l, 'label', 0);
            if ($label !== '') { echo '<li><a href="' . hc_e(hc_url(hc_i($l, 'link', 1))) . '">' . hc_e($label) . '</a></li>'; }
        }
        echo '</ul></div>';
    }
    echo '<div><h3 class="f-h">' . hc_e(s('footer_contact_title', 'Contact Us')) . '</h3><ul class="f-contact">';
    echo '<li>' . hc_icon('phone') . '<a href="' . hc_e(hc_tel()) . '" data-track="call">' . hc_e(s('mobile')) . '</a></li>';
    echo '<li>' . hc_icon('whatsapp') . '<a href="' . hc_e(hc_wa()) . '" target="_blank" rel="noopener" data-track="whatsapp">WhatsApp: ' . hc_e(s('mobile')) . '</a></li>';
    echo '<li>' . hc_icon('mail') . '<a href="mailto:' . hc_e(s('email')) . '">' . hc_e(s('email')) . '</a></li>';
    echo '<li>' . hc_icon('pin') . '<span>' . hc_e(s('address')) . '</span></li>';
    echo '<li>' . hc_icon('clock') . '<span>' . hc_e(s('working_hours')) . '</span></li>';
    echo '</ul></div></div>';
    echo '<div class="copy">' . hc_e(str_replace('{year}', date('Y'), s('copyright', '© {year}'))) . '</div>';
    echo '</div></footer>';

    hc_floating();
    hc_mobile_bar();
    hc_popup();

    echo '<script src="' . hc_e(hc_asset('assets/site/main.js')) . '" defer></script>';
    echo hc_tracking('bodyend');
    echo '</body></html>';
}

function hc_floating()
{
    $f = s('floating', array());
    $g = function ($k, $d) use ($f) { return is_array($f) && isset($f[$k]) ? $f[$k] : $d; };
    if (!on($g('floating_enabled', true))) { return; }
    $call = on($g('call_enabled', true));
    $wa = on($g('whatsapp_enabled', true));
    if (!$call && !$wa) { return; }

    $side = $g('position_side', 'right') === 'left' ? 'left' : 'right';
    $cls = 'floating fb-' . $side . ' fb-' . ($g('layout', 'vertical') === 'horizontal' ? 'horizontal' : 'vertical');
    $anim = preg_replace('/[^a-z]/', '', (string)$g('animation', 'pulse'));
    $cls .= ' anim-' . ($anim ? $anim : 'none') . ' speed-' . preg_replace('/[^a-z]/', '', (string)$g('animation_speed', 'normal'));
    if (!on($g('show_on_mobile', true))) { $cls .= ' hide-mobile'; }
    if (!on($g('show_on_desktop', true))) { $cls .= ' hide-desktop'; }
    if (on(s('mobile_bar.enabled', true))) { $cls .= ' has-mbar'; }
    $num = function ($v) { return (int)preg_replace('/[^0-9]/', '', (string)$v); };
    $style = '--fb-x:' . $num($g('position_x', 20)) . 'px;--fb-y:' . $num($g('position_y', 24)) . 'px;--fb-gap:' . $num($g('gap', 12)) . 'px;'
        . '--fb-size:' . $num($g('size', 58)) . 'px;--fb-msize:' . $num($g('mobile_size', 52)) . 'px;--fb-icon:' . $num($g('icon_size', 26)) . 'px;'
        . '--fb-call:' . hc_e($g('call_color', '#1f5c99')) . ';--fb-wa:' . hc_e($g('whatsapp_color', '#25D366')) . ';';
    $tip = on($g('tooltip_enabled', true));

    echo '<div class="' . $cls . '" style="' . $style . '">';
    if ($wa) {
        echo '<a class="fb fb-wa" href="' . hc_e(hc_wa()) . '" target="_blank" rel="noopener" aria-label="' . hc_e($g('whatsapp_tooltip_text', 'WhatsApp')) . '" data-track="whatsapp">' . hc_icon('whatsapp') . ($tip ? '<span class="tip">' . hc_e($g('whatsapp_tooltip_text', 'Chat on WhatsApp')) . '</span>' : '') . '</a>';
    }
    if ($call) {
        echo '<a class="fb fb-call" href="' . hc_e(hc_tel()) . '" aria-label="' . hc_e($g('call_tooltip_text', 'Call')) . '" data-track="call">' . hc_icon('phone') . ($tip ? '<span class="tip">' . hc_e($g('call_tooltip_text', 'Call Now')) . '</span>' : '') . '</a>';
    }
    echo '</div>';
}

function hc_mobile_bar()
{
    if (!on(s('mobile_bar.enabled', true))) { return; }
    echo '<div class="mbar">'
        . '<a class="mb-call" href="' . hc_e(hc_tel()) . '" data-track="call">' . hc_icon('phone') . '<span>' . hc_e(s('mobile_bar.call_text', 'Call')) . '</span></a>'
        . '<a class="mb-wa" href="' . hc_e(hc_wa()) . '" target="_blank" rel="noopener" data-track="whatsapp">' . hc_icon('whatsapp') . '<span>' . hc_e(s('mobile_bar.whatsapp_text', 'WhatsApp')) . '</span></a>'
        . '<button type="button" class="mb-main" data-popup data-item="">' . hc_e(s('mobile_bar.main_text', 'Book Now')) . '</button>'
        . '</div>';
}

function hc_popup()
{
    echo '<div class="pop" id="hcPop" aria-hidden="true"><div class="pop-box" role="dialog" aria-modal="true" aria-labelledby="popTitle">';
    echo '<button type="button" class="pop-x" aria-label="Close" data-close-pop>×</button>';
    echo '<h2 id="popTitle" data-general="' . hc_e(s('popup_heading', 'Get a Free Call Back')) . '" data-prefix="' . hc_e(s('popup_item_prefix', 'Book')) . '">' . hc_e(s('popup_heading', 'Get a Free Call Back')) . '</h2>';
    echo '<p class="pop-sub">' . hc_e(s('popup_subtext', '')) . '</p>';
    echo '<div class="pop-for" hidden><small>You are enquiring for</small><b class="pop-item"></b><span class="pop-det"></span></div>';
    echo hc_form('popForm', 'Popup Form', true);
    echo '</div></div>';
}
