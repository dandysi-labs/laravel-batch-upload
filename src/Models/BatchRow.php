<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchRow extends Model
{
    public const STATUS_INVALID = 'Invalid';
    public const STATUS_VALID = 'Valid';
    public const STATUS_FAILED = 'Failed';
    public const STATUS_PROCESSED = 'Processed';

    public const STATUSES = [
        self::STATUS_VALID,
        self::STATUS_INVALID,
        self::STATUS_PROCESSED,
        self::STATUS_FAILED
    ];

    public const LABELS = [
        'id' => 'ID',
        'status' => 'Status',
        'content' => 'Content',
        'errors' => 'Errors'
    ];

    protected $casts = [
        'content' => 'array',
        'errors' => 'array',
    ];

    protected $fillable = [
        'content',
        'errors',
        'status',
        'batch_id'
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function scopeValid(Builder $query): void
    {
        $query->where('status', '=', BatchRow::STATUS_VALID);
    }
}
