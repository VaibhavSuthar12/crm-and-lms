<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'company_website',
        'company_size',
        'industry',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'assigned_to',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
