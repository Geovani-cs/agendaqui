<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return Service::with('vehicleTypes')->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $service = Service::create($data);
        $service->vehicleTypes()->sync($data['vehicle_type_ids'] ?? []);
        return response()->json($service->load('vehicleTypes'), 201);
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validated($request);
        $service->update($data);
        $service->vehicleTypes()->sync($data['vehicle_type_ids'] ?? []);
        return $service->load('vehicleTypes');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'cod' => ['nullable', 'string'],
            'vehicle_type_ids' => ['nullable', 'array'],
            'vehicle_type_ids.*' => ['exists:vehicle_types,id'],
            'name' => ['required', 'string'],
            'value' => ['required', 'numeric', 'min:0'],
            'execution_time' => ['required', 'integer', 'min:0'],
        ]);
    }
}
