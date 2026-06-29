<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    const PRIORITY_LOW    = 'Low';
    const PRIORITY_MEDIUM = 'Medium';
    const PRIORITY_HIGH   = 'High';

    const STATUS_PENDING     = 'Pending';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_COMPLETED   = 'Completed';
    const STATUS_CANCELLED   = 'Cancelled';

    const PRIORITIES = [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH];
    const STATUSES   = [self::STATUS_PENDING, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED, self::STATUS_CANCELLED];

    protected $fillable = [
        'title',
        'description',
        'lead_id',
        'customer_id',
        'assigned_to',
        'created_by',
        'priority',
        'status',
        'due_date',
        'reminder_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date'     => 'date',
            'reminder_at'  => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeTodayDue($query)
    {
        return $query->whereDate('due_date', today())
                     ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
