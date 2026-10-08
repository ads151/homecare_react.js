<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
        'is_system' => 'boolean',
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'in_sitemap' => 'boolean',
    ];

    public const TEMPLATES = [
        'builder' => 'Page Builder (sections)',
        'home' => 'Home Page design',
        'about' => 'About Page design',
        'contact' => 'Contact Page design',
        'listing' => 'All Services (cards) design',
        'legal' => 'Long Text (Privacy / Terms) design',
        'thank-you' => 'Thank You Page design',
    ];

    public function url(): string
    {
        return \App\Support\Frontend::pageUrl($this->slug);
    }
}
