<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssistanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_code',
        'user_id',
        'demarche_id',
        'demarche_title',
        'full_name',
        'email',
        'phone',
        'nationality',
        'location_status',
        'city_country',
        'purpose',
        'details',
        'amount',
        'currency',
        'payment_method',
        'payment_status',
        'payment_reference',
        'stripe_payment_intent_id',
        'card_last4',
        'payment_proof_url',
        'payment_proof_public_id',
        'status',
        'admin_feedback',
        'admin_attachment_url',
        'admin_attachment_name',
        'admin_attachment_public_id',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function demarche(): BelongsTo
    {
        return $this->belongsTo(Demarche::class);
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssistanceDocument::class);
    }
}

