<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToTenant;

    protected $fillable = ['description', 'value', 'spent_on', 'user_id'];
    protected $casts = ['value' => 'decimal:2', 'spent_on' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
