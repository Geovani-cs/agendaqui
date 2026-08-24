<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_name', 'company_pix', 'message_scheduling', 'message_rescheduling', 'message_completion',
    ];

    // Sempre trabalha com a única linha de configuração do tenant.
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'company_name' => 'Meu Lava-Rápido',
            'message_scheduling' => 'Olá {nome}! Seu agendamento no {empresa} está confirmado para {data} às {hora}. Serviço: {servico}. Obrigado pela preferência!',
            'message_rescheduling' => 'Olá {nome}! Seu agendamento no {empresa} foi remarcado para {data} às {hora}. Serviço: {servico}. Qualquer dúvida, estamos à disposição!',
            'message_completion' => 'Olá {nome}! Seu {servico} foi concluído. Valor: {valor}. Pague via PIX: {pix}. Agradecemos a confiança!',
        ]);
    }

    public function render(string $field, array $vars): string
    {
        $tpl = (string) ($this->{$field} ?? '');
        foreach ($vars as $k => $v) {
            $tpl = str_replace('{' . $k . '}', (string) $v, $tpl);
        }
        return $tpl;
    }
}
