<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'slug', 'name', 'status',
        'monthly_fee', 'due_date', 'status_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date'          => 'date',
            'status_changed_at' => 'datetime',
            'monthly_fee'       => 'decimal:2',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Estados: active -> warning -> read_only -> blocked
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function isReadOnly(): bool
    {
        return $this->status === 'read_only';
    }
}
