<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // 1) Toda leitura passa a filtrar pelo tenant atual, automaticamente.
        static::addGlobalScope(new TenantScope());

        // 2) Toda criacao preenche o tenant_id sozinho.
        static::creating(function (Model $model) {
            if (empty($model->tenant_id) && app()->bound('currentTenant')) {
                $model->tenant_id = app('currentTenant')->id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
