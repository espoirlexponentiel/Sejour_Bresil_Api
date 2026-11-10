<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistanceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'assistance_request_id',
        'doc_key',
        'doc_label',
        'file_url',
        'public_id',
        'resource_type',
        'original_name',
        'mime_type',
    ];

    public function assistanceRequest(): BelongsTo
    {
        return $this->belongsTo(AssistanceRequest::class);
    }
}

