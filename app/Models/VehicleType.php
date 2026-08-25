<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_vehicle_type');
    }
}
