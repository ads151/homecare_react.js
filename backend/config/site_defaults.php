<?php

/*
 * Default website settings. These are copied into the database during
 * installation; after that everything is edited from Admin → Site Settings.
 */
return [

    // Business
    'business_name' => 'Home Care',
    'tagline' => 'Compassionate In-Home Care',
    'logo' => 'images/general/logo.png',
    'show_name_with_logo' => true,
    'favicon' => 'images/general/favicon.png',
    'apple_touch_icon' => 'images/general/apple-touch-icon.png',
    'website_url' => '',

    // Local SEO (Google business details)
    'schema_type' => 'MedicalBusiness',
    'city' => 'East Delhi',
    'state' => 'Delhi',
    'pincode' => '110051',
    'schema_opening_hours' => 'Mo-Su 00:00-23:59',
    'price_range' => '₹₹',
    'default_og_image' => '',
    'google_site_verification' => '',
    'bing_site_verification' => '',

    // Contact
    'mobile' => '98716 86357',
    'whatsapp_number' => '919871686357',
    'email' => 'homecare4u@gmail.com',
    'address' => 'G-113/4, Jagatpuri, Krishna Nagar, East Delhi 110051',
    'working_hours' => 'Open 24 Hours, 7 Days',

    // WhatsApp messages
    'whatsapp_message' => 'Hi Home Care, I need home care service. Please call me back.',
    'whatsapp_card_message' => 'Hi Home Care, I want details for {item}. Please call me back.',

    // Colours
    'colors' => [
        'primary' => '#1f5c99',
        'secondary' => '#1b8a6b',
        'dark' => '#0f2a52',
        'accent' => '#8cc63f',
        'light' => '#eef6f4',
    ],

    // Buttons
    'buttons' => [
        'main_text' => 'Book Now',
        'main_bg' => '#2c9a55',
        'main_color' => '#ffffff',
        'call_text' => 'Call Now',
        'call_bg' => '#1f5c99',
        'call_color' => '#ffffff',
        'whatsapp_text' => 'WhatsApp',
        'whatsapp_bg' => '#25D366',
        'whatsapp_color' => '#ffffff',
        'submit_text' => 'Get Free Call Back',
        'submit_bg' => '#2c9a55',
        'submit_color' => '#ffffff',
        'quote_text' => 'Get Free Quote',
        'quote_bg' => '#ffffff',
        'quote_color' => '#0f2a52',
        'sending_text' => 'Sending…',
        'card_show_call' => true,
        'card_show_whatsapp' => true,
        'email_action_word' => 'Booking Enquiry',
    ],

    // Header
    'topbar_show' => true,
    'topbar_text' => 'Nurses & caretakers available today in East Delhi & NCR',
    'menu' => [
        ['label' => 'Home', 'link' => ''],
        ['label' => 'Our Services', 'link' => 'home-care-services'],
        ['label' => 'About Us', 'link' => 'about'],
        ['label' => 'Contact Us', 'link' => 'contact'],
    ],
    'header_phone_show' => true,
    'header_phone_label' => 'Call 24×7',
    'header_button_show' => true,
    'header_button_text' => 'Book a Nurse',

    // Popup
    'popup_heading' => 'Get a Free Call Back',
    'popup_item_prefix' => 'Book',
    'popup_subtext' => 'Our care manager will call you back within 10 minutes.',

    // CTA banner
    'cta_heading' => 'Need a nurse or caretaker today?',
    'cta_text' => 'Talk to our care manager now — free consultation, no obligation.',
    'cta_image' => 'images/pages/cta.jpg',

    // Footer
    'footer_about' => 'Trusted home healthcare in East Delhi & NCR — trained nurses, ICU at home, elder care, patient care, mother & baby care and physiotherapy at your doorstep.',
    'footer_quick_title' => 'Quick Links',
    'footer_quick_links' => [
        ['label' => 'Home', 'link' => ''],
        ['label' => 'Our Services', 'link' => 'home-care-services'],
        ['label' => 'About Us', 'link' => 'about'],
        ['label' => 'Contact Us', 'link' => 'contact'],
    ],
    'footer_info_title' => 'Information',
    'footer_info_links' => [
        ['label' => 'Privacy Policy', 'link' => 'privacy-policy'],
        ['label' => 'Terms & Conditions', 'link' => 'terms'],
    ],
    'footer_contact_title' => 'Contact Us',
    'social' => [
        'facebook' => '',
        'instagram' => '',
        'youtube' => '',
        'linkedin' => '',
        'x' => '',
    ],
    'copyright' => '© {year} Home Care. All rights reserved.',

    // Form fields (one list for all forms)
    'form_fields' => [
        ['name' => 'name', 'label' => 'Patient / Your Name', 'type' => 'text', 'required' => true, 'placeholder' => 'Enter your name', 'options' => '', 'short_form' => true],
        ['name' => 'mobile', 'label' => 'Mobile Number', 'type' => 'tel', 'required' => true, 'placeholder' => '10-digit mobile number', 'options' => '', 'short_form' => true],
        ['name' => 'service', 'label' => 'Service Needed', 'type' => 'select', 'required' => true, 'placeholder' => 'Select service', 'options' => 'services', 'short_form' => true],
        ['name' => 'area', 'label' => 'Area / Locality', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. Laxmi Nagar', 'options' => '', 'short_form' => true],
        ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Patient condition, shift needed, start date…', 'options' => '', 'short_form' => false],
    ],
    'privacy_note' => 'Your details are 100% safe with us. We never share them.',

    // Email / SMTP
    'mail_to' => 'homecare4u@gmail.com',
    'mail_cc' => '',
    'smtp_on' => true,
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 465,
    'smtp_secure' => 'ssl',
    'smtp_username' => 'homecare4u@gmail.com',
    'smtp_password' => '',
    'from_email' => '',
    'from_name' => 'Home Care Website',
    'autoreply_on' => false,
    'autoreply_subject' => 'Thank you for contacting Home Care',
    'autoreply_message' => "Dear {name},\n\nThank you for your enquiry for {item}. Our care manager will call you shortly.\n\nFor urgent help please call {phone}.\n\nRegards,\nHome Care",

    // Thank-you page buttons
    'thankyou' => [
        'call_text' => 'CALL NOW',
        'call_bg' => '#1f5c99',
        'call_color' => '#ffffff',
        'home_text' => 'Back to Home',
    ],

    // Floating buttons
    'floating' => [
        'floating_enabled' => true,
        'call_enabled' => true,
        'whatsapp_enabled' => true,
        'position_side' => 'right',
        'position_x' => 20,
        'position_y' => 24,
        'gap' => 12,
        'layout' => 'vertical',
        'size' => 58,
        'mobile_size' => 52,
        'icon_size' => 26,
        'call_color' => '#1f5c99',
        'whatsapp_color' => '#25D366',
        'animation' => 'pulse',
        'animation_speed' => 'normal',
        'show_on_mobile' => true,
        'show_on_desktop' => true,
        'tooltip_enabled' => true,
        'call_tooltip_text' => 'Call Now',
        'whatsapp_tooltip_text' => 'Chat on WhatsApp',
    ],

    // Mobile bottom bar
    'mobile_bar' => [
        'enabled' => true,
        'call_text' => 'Call',
        'whatsapp_text' => 'WhatsApp',
        'main_text' => 'Book Nurse',
    ],

    // Tracking codes
    'tracking' => [
        'head_code_on' => true,
        'head_code' => '',
        'bodystart_code_on' => true,
        'bodystart_code' => '',
        'bodyend_code_on' => true,
        'bodyend_code' => '',
        'thankyou_code_on' => true,
        'thankyou_code' => '',
    ],

    // Next.js website address (headless mode)
    'frontend_url' => '',

    // robots.txt extra lines
    'robots_extra' => '',
];
