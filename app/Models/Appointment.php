<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'owner_name', 'phone', 'plate', 'vehicle_type_id',
        'model', 'scheduled_at', 'status', 'collaborator_id', 'created_by',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    // Campos calculados expostos no JSON.
    protected $appends = ['value', 'duration', 'date', 'time', 'is_late', 'commission_total'];

    public function services()
    {
        return $this->belongsToMany(Service::class)
            ->withPivot(['price', 'execution_time', 'commission'])->withTimestamps();
    }

    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function photos()
    {
        return $this->hasMany(AppointmentPhoto::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Valor total do agendamento (soma dos preços snapshot).
    public function getValueAttribute(): float
    {
        return (float) $this->services->sum(fn ($s) => (float) $s->pivot->price);
    }

    // Duração total em minutos.
    public function getDurationAttribute(): int
    {
        return (int) $this->services->sum(fn ($s) => (int) $s->pivot->execution_time);
    }

    // Comissão total devida ao colaborador por este serviço.
    public function getCommissionTotalAttribute(): float
    {
        return (float) $this->services->sum(fn ($s) => (float) $s->pivot->commission);
    }

    public function getDateAttribute(): ?string
    {
        return $this->scheduled_at?->toDateString();
    }

    public function getTimeAttribute(): ?string
    {
        return $this->scheduled_at?->format('H:i');
    }

    // "atrasado" é derivado: aguardando e já passou do horário.
    public function getIsLateAttribute(): bool
    {
        return $this->status === 'aguardando'
            && $this->scheduled_at
            && $this->scheduled_at->isPast();
    }

    public function scopeConcluded($q)
    {
        return $q->where('status', 'concluido');
    }
}
