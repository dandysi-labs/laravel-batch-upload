<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class Batch extends Model
{
    public const STATUS_CREATING = 'Creating';
    public const STATUS_PENDING  = 'Pending';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_DISPATCHED = 'Dispatched';

    public const LABELS = [
        'id' => 'ID',
        'created_at' => 'Created At',
        'status' => 'Status',
        'processor' => 'Processor',
        'num_rows' => 'Num Rows',
        'num_invalid_rows' => 'Invalid Rows',
        'num_failed_rows' => 'Failed Rows',
        'schedule_at' => 'Schedule At',
        'user' => 'User'
    ];

    protected $attributes = [
        'num_rows' => 0,
        'num_invalid_rows' => 0,
        'num_failed_rows' => 0
    ];
    protected $fillable = [
        'status',
        'processor',
        'schedule_at',
        'user'
    ];

    protected $casts = [
        'schedule_at' => 'datetime'
    ];

    public function rows(): HasMany
    {
        return $this->hasMany(BatchRow::class);
    }

    public function assertStatus(string $status): void
    {
        throw_if(
            $status !== $this->status,
            RuntimeException::class,
            sprintf(
                'Expected status "%s", actual "%s".',
                $status,
                $this->status
            )
        );
    }

    public function scopeDispatchable(Builder $query): void
    {
        $query
            ->where('status', '=', self::STATUS_APPROVED)
            ->where('schedule_at', '<=', now())
            ->orderBy('schedule_at', 'asc')
        ;
    }
}
