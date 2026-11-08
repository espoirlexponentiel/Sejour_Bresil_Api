<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'stay_id',
        'stay_title',
        'customer_name',
        'email',
        'phone',
        'travelers',
        'travel_date',
        'total_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'travelers' => 'integer',
        'total_price' => 'float',
    ];

    public function stay(): BelongsTo
    {
        return $this->belongsTo(Stay::class);
    }
}
