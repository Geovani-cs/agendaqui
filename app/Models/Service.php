<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use BelongsToTenant;

    protected $fillable = ['cod', 'vehicle_type_id', 'name', 'value', 'execution_time'];
    protected $casts = ['value' => 'decimal:2', 'execution_time' => 'integer'];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function collaborators()
    {
        return $this->belongsToMany(Collaborator::class)
            ->withPivot('commission')->withTimestamps();
    }
}
