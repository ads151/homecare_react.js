<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'array',
        'mail_sent' => 'boolean',
    ];

    public const STATUSES = [
        'new' => 'New',
        'called' => 'Called',
        'follow_up' => 'Follow-up',
        'converted' => 'Converted',
        'closed' => 'Closed / Not Interested',
    ];
}
