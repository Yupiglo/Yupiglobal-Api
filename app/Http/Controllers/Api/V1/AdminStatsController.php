<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = ($user?->role ?? 'user') === 'admin';
        if (!$isAdmin) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $usersCount = User::query()->count();
        $productsCount = Product::query()->count();
        $ordersCount = Order::query()->count();
        $revenueTotal = (float) Order::query()->sum('total_order_price');

        $activeNow = (int) DB::table('personal_access_tokens')
            ->whereNotNull('last_used_at')
            ->where('last_used_at', '>=', now()->subMinutes(15))
            ->count();

        $recentOrders = Order::query()
            ->with(['user'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => (string) $order->id,
                    'total' => (float) $order->total_order_price,
                    'status' => (string) $order->order_status,
                    'createdAt' => optional($order->created_at)->toISOString(),
                    'user' => [
                        'id' => (string) $order->user_id,
                        'name' => (string) ($order->user?->name ?? ''),
                        'email' => (string) ($order->user?->email ?? ''),
                    ],
                ];
            })
            ->values();

        return response()->json([
            'message' => 'success',
            'stats' => [
                'revenueTotal' => $revenueTotal,
                'usersCount' => $usersCount,
                'productsCount' => $productsCount,
                'ordersCount' => $ordersCount,
                'salesCount' => $ordersCount,
                'activeNow' => $activeNow,
            ],
            'recentOrders' => $recentOrders,
        ], 200);
    }
}
