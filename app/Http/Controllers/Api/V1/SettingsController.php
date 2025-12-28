<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::query()->orderByDesc('id')->first();

        $payload = [
            '_id' => $settings ? (string) $settings->id : '0',
            'siteName' => (string) ($settings?->site_name ?? 'Yupi Store'),
            'logo' => (string) ($settings?->logo ?? ''),
            'phone' => (string) ($settings?->phone ?? ''),
            'email' => (string) ($settings?->email ?? 'support@yupi.com'),
            'address' => (string) ($settings?->address ?? ''),
            'facebook' => $settings?->facebook,
            'instagram' => $settings?->instagram,
            'twitter' => $settings?->twitter,
            'youtube' => $settings?->youtube,
        ];

        return response()->json([
            'message' => 'success',
            'settings' => $payload,
        ], 200);
    }

    public function general()
    {
        $settings = Setting::query()->orderByDesc('id')->first();

        return response()->json([
            'siteName' => (string) ($settings?->site_name ?? 'Yupi Store'),
            'logo' => (string) ($settings?->logo ?? ''),
            'phone' => (string) ($settings?->phone ?? ''),
            'email' => (string) ($settings?->email ?? 'support@yupi.com'),
            'address' => (string) ($settings?->address ?? ''),
            'facebook' => $settings?->facebook,
            'instagram' => $settings?->instagram,
            'twitter' => $settings?->twitter,
            'youtube' => $settings?->youtube,
        ], 200);
    }
}
