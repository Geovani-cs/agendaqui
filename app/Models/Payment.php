<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use BelongsToTenant;

    protected $fillable = ['collaborator_id', 'amount', 'paid_on', 'manual', 'user_id'];
    protected $casts = ['amount' => 'decimal:2', 'paid_on' => 'date', 'manual' => 'boolean'];

    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class);
    }
}
