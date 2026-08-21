<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return Service::with('vehicleType')->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        return response()->json(Service::create($this->validated($request)), 201);
    }

    public function update(Request $request, Service $service)
    {
        $service->update($this->validated($request));
        return $service->load('vehicleType');
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
            'vehicle_type_id' => ['nullable', 'exists:vehicle_types,id'],
            'name' => ['required', 'string'],
            'value' => ['required', 'numeric', 'min:0'],
            'execution_time' => ['required', 'integer', 'min:0'],
        ]);
    }
}
