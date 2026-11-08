<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stay extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'destination',
        'region',
        'duration',
        'days',
        'price',
        'rating',
        'reviews_count',
        'image',
        'category',
        'tag',
        'featured',
        'status',
        'summary',
        'highlights',
        'included',
        'dates',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'days' => 'integer',
        'price' => 'float',
        'rating' => 'float',
        'reviews_count' => 'integer',
        'highlights' => 'array',
        'included' => 'array',
        'dates' => 'array',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
