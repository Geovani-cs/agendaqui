<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'username', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['password' => 'hashed'];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // O dono da plataforma (landlord) nao pertence a nenhum tenant.
    public function isSuperAdmin(): bool
    {
        return $this->tenant_id === null && $this->role === 'super_admin';
    }
}
