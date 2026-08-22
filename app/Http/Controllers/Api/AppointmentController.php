<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Collaborator;
use App\Models\Service;
use App\Models\Setting;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function __construct(private ScheduleService $schedule) {}

    // Lista/consulta com filtros. Fotos vêm "leves" (só id) — o conteúdo é carregado no show().
    public function index(Request $request)
    {
        $q = Appointment::with(['services', 'collaborator', 'vehicleType', 'photos:id,appointment_id']);

        if ($request->filled('name')) {
            $q->where('owner_name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('plate')) {
            $q->where('plate', 'like', '%' . $request->plate . '%');
        }
        if ($request->filled('vehicle_type_id')) {
            $q->where('vehicle_type_id', $request->vehicle_type_id);
        }
        if ($request->filled('collaborator_id')) {
            $request->collaborator_id === '_sem_'
                ? $q->whereNull('collaborator_id')
                : $q->where('collaborator_id', $request->collaborator_id);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $q->whereDate('scheduled_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('scheduled_at', '<=', $request->to);
        }

        return $q->orderByDesc('scheduled_at')->get();
    }

    // Detalhe com as fotos completas (data URL base64).
    public function show(Appointment $appointment)
    {
        return $appointment->load(['services', 'collaborator', 'vehicleType', 'photos']);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $start = Carbon::parse($data['date'] . ' ' . $data['time']);
        $duration = $this->schedule->durationFor($data['service_ids']);

        if (! $request->boolean('ignore_conflict')) {
            $conflict = $this->schedule->conflict($start, $duration);
            if ($conflict) {
                return response()->json([
                    'conflict' => true,
                    'message' => 'Conflito de horário.',
                    'with' => [
                        'id' => $conflict->id,
                        'owner_name' => $conflict->owner_name,
                        'time' => $conflict->time,
                    ],
                ], 409);
            }
        }

        $appointment = DB::transaction(function () use ($data, $start, $request) {
            $appt = Appointment::create([
                'owner_name' => $data['owner_name'],
                'phone' => $data['phone'],
                'plate' => $data['plate'] ?? null,
                'vehicle_type_id' => $data['vehicle_type_id'] ?? null,
                'model' => $data['model'] ?? null,
                'scheduled_at' => $start,
                'status' => 'aguardando',
                'created_by' => $request->user()?->id,
            ]);

            $this->attachServices($appt, $data['service_ids']);
            $this->savePhotos($appt, $request->input('photos', []));

            return $appt;
        });

        return response()->json([
            'appointment' => $appointment->load(['services', 'collaborator', 'photos:id,appointment_id']),
            'whatsapp' => $this->schedulingMessage($appointment),
        ], 201);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $data = $this->validated($request);
        $start = Carbon::parse($data['date'] . ' ' . $data['time']);
        $duration = $this->schedule->durationFor($data['service_ids']);

        if (! $request->boolean('ignore_conflict')) {
            $conflict = $this->schedule->conflict($start, $duration, $appointment->id);
            if ($conflict) {
                return response()->json([
                    'conflict' => true,
                    'with' => ['owner_name' => $conflict->owner_name, 'time' => $conflict->time],
                ], 409);
            }
        }

        DB::transaction(function () use ($appointment, $data, $start, $request) {
            $appointment->update([
                'owner_name' => $data['owner_name'],
                'phone' => $data['phone'],
                'plate' => $data['plate'] ?? null,
                'vehicle_type_id' => $data['vehicle_type_id'] ?? null,
                'model' => $data['model'] ?? null,
                'scheduled_at' => $start,
            ]);
            $this->attachServices($appointment, $data['service_ids'], $appointment->collaborator);
            $this->savePhotos($appointment, $request->input('photos', [])); // anexa fotos novas
        });

        return $appointment->load(['services', 'collaborator', 'photos:id,appointment_id']);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->noContent();
    }

    // Inicia o serviço -> EM ANDAMENTO.
    public function start(Appointment $appointment)
    {
        $appointment->update(['status' => 'andamento']);
        return $appointment->load(['services', 'collaborator']);
    }

    // Cancela o agendamento (soft: preserva o registro, sai do faturamento).
    public function cancel(Appointment $appointment)
    {
        $appointment->update(['status' => 'cancelado']);
        return $appointment->load(['services', 'collaborator']);
    }

    // Conclui o serviço, registra o colaborador e a comissão (snapshot).
    public function complete(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'collaborator_id' => ['nullable', 'exists:collaborators,id'],
        ]);

        DB::transaction(function () use ($appointment, $data) {
            $appointment->update([
                'status' => 'concluido',
                'collaborator_id' => $data['collaborator_id'] ?? null,
            ]);

            $collaborator = $data['collaborator_id']
                ? Collaborator::with('services')->find($data['collaborator_id'])
                : null;

            foreach ($appointment->services as $service) {
                $commission = $collaborator ? $collaborator->commissionFor($service->id) : 0;
                $appointment->services()->updateExistingPivot($service->id, ['commission' => $commission]);
            }
        });

        $appointment->load(['services', 'collaborator']);

        return response()->json([
            'appointment' => $appointment,
            'whatsapp' => $this->completionMessage($appointment),
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'owner_name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'plate' => ['nullable', 'string'],
            'vehicle_type_id' => ['nullable', 'exists:vehicle_types,id'],
            'model' => ['nullable', 'string'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['exists:services,id'],
            'photos' => ['nullable', 'array'],
        ]);
    }

    private function attachServices(Appointment $appointment, array $serviceIds, ?Collaborator $collaborator = null): void
    {
        $collaborator?->loadMissing('services');
        $services = Service::whereIn('id', $serviceIds)->get();

        $sync = [];
        foreach ($services as $s) {
            $sync[$s->id] = [
                'price' => $s->value,
                'execution_time' => $s->execution_time,
                'commission' => $collaborator ? $collaborator->commissionFor($s->id) : 0,
            ];
        }
        $appointment->services()->sync($sync);
    }

    // Opcao B: guarda a imagem (data URL base64) direto no banco.
    private function savePhotos(Appointment $appointment, array $photos): void
    {
        foreach ($photos as $photo) {
            if (! is_string($photo) || ! str_contains($photo, 'base64,')) {
                continue;
            }
            $appointment->photos()->create(['path' => '', 'data' => $photo]);
        }
    }

    private function schedulingMessage(Appointment $a): array
    {
        $setting = Setting::current();
        $text = $setting->render('message_scheduling', [
            'nome' => $a->owner_name,
            'empresa' => $setting->company_name,
            'data' => $a->scheduled_at->format('d/m/Y'),
            'hora' => $a->time,
            'servico' => $a->services->pluck('name')->join(', '),
        ]);
        return ['phone' => $a->phone, 'text' => $text];
    }

    private function completionMessage(Appointment $a): array
    {
        $setting = Setting::current();
        $text = $setting->render('message_completion', [
            'nome' => $a->owner_name,
            'empresa' => $setting->company_name,
            'servico' => $a->services->pluck('name')->join(', '),
            'valor' => 'R$ ' . number_format($a->value, 2, ',', '.'),
            'pix' => $setting->company_pix ?: '(configure o PIX)',
        ]);
        return ['phone' => $a->phone, 'text' => $text];
    }
}
