<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /** Same array shape the website templates expect. */
    public function toCard(): array
    {
        return [
            'title' => $this->title,
            'image' => $this->image,
            'image_alt' => $this->image_alt,
            'badge' => $this->badge,
            'label' => $this->label,
            'category' => $this->category,
            'text' => $this->text,
            'features' => array_values(array_filter((array) $this->features, fn ($f) => trim((string) $f) !== '')),
            'price_prefix' => $this->price_prefix,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'price_note' => $this->price_note,
            'button_text' => $this->button_text,
            'order' => $this->sort_order,
            'show' => $this->is_active,
        ];
    }
}
