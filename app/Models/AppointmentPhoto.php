<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppointmentPhoto extends Model
{
    use BelongsToTenant;

    protected $fillable = ['appointment_id', 'path'];
    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
