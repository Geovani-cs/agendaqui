<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index()
    {
        return VehicleType::orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'unique:vehicle_types,name']]);
        return response()->json(VehicleType::create($data), 201);
    }

    public function update(Request $request, VehicleType $vehicleType)
    {
        $data = $request->validate(['name' => ['required', 'string', 'unique:vehicle_types,name,' . $vehicleType->id]]);
        $vehicleType->update($data);
        return $vehicleType;
    }

    public function destroy(VehicleType $vehicleType)
    {
        $vehicleType->delete();
        return response()->noContent();
    }
}
