<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Collaborator extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'cpf', 'phone', 'pix'];

    // Serviços com o ganho (comissão) que este colaborador recebe em cada um.
    public function services()
    {
        return $this->belongsToMany(Service::class)
            ->withPivot('commission')->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Comissão que este colaborador ganha em um determinado serviço.
    public function commissionFor(int $serviceId): float
    {
        $svc = $this->services->firstWhere('id', $serviceId);
        return $svc ? (float) $svc->pivot->commission : 0.0;
    }
}
