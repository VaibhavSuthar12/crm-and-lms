<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    const TYPE_LEAD_CREATED        = 'Lead Created';
    const TYPE_LEAD_ASSIGNED       = 'Lead Assigned';
    const TYPE_STATUS_CHANGED      = 'Status Changed';
    const TYPE_CUSTOMER_CONVERTED  = 'Customer Converted';
    const TYPE_NOTE_ADDED          = 'Note Added';
    const TYPE_TASK_CREATED        = 'Task Created';
    const TYPE_TASK_COMPLETED      = 'Task Completed';

    protected $fillable = [
        'user_id',
        'lead_id',
        'customer_id',
        'type',
        'description',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
