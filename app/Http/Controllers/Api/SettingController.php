<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show()
    {
        return Setting::current();
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string'],
            'company_pix' => ['nullable', 'string'],
            'message_scheduling' => ['nullable', 'string'],
            'message_completion' => ['nullable', 'string'],
        ]);
        $setting = Setting::current();
        $setting->update($data);
        return $setting;
    }
}
