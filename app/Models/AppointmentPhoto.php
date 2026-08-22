<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AppointmentPhoto extends Model
{
    use BelongsToTenant;

    // 'data' guarda a imagem como data URL base64 (Opcao B).
    protected $fillable = ['appointment_id', 'path', 'data'];
}
