<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AidCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type_badge',
        'city',
        'city_slug',
        'services',
        'address',
        'hours',
        'phone',
        'website',
    ];

    protected $casts = [
        'services' => 'array',
    ];
}
