<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demarche extends Model
{
    use HasFactory;

    protected $fillable = [
        'step_number',
        'priority',
        'icon',
        'image_url',
        'title',
        'subtitle',
        'description',
        'delay',
        'cost',
        'assistance_price',
        'button_text',
        'guide_url',
        'official_source',
        'questions',
        'checklist',
        'steps',
        'faqs',
        'assistance_purposes',
        'status',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'assistance_price' => 'float',
        'questions' => 'array',
        'checklist' => 'array',
        'steps' => 'array',
        'faqs' => 'array',
        'assistance_purposes' => 'array',
    ];
}
