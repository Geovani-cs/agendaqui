<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Closing extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'closed_on', 'gross', 'expenses_total',
        'collaborators_total', 'net', 'closed_by',
    ];

    protected $casts = [
        'closed_on' => 'date',
        'gross' => 'decimal:2',
        'expenses_total' => 'decimal:2',
        'collaborators_total' => 'decimal:2',
        'net' => 'decimal:2',
    ];
}
