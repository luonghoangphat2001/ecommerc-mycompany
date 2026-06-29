<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Order;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Product;
use App\Models\TaxClass;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $latestOrders = Order::latest('id')->take(6)->get(['id', 'number', 'status', 'total', 'currency', 'created_at']);
        $orderStatusCounts = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),
            'totalRevenue' => Order::sum('total'),
            'pendingOrders' => Order::whereIn('status', ['pending', 'new', 'processing'])->count(),
            'paidPayments' => Payment::where('status', 'paid')->sum('amount'),
            'totalPosts' => Post::count(),
            'totalPages' => Page::count(),
            'totalMedia' => Media::count(),
            'totalTaxClasses' => TaxClass::count(),
            'totalWebhooks' => Webhook::count(),
            'latestOrders' => $latestOrders,
            'orderStatusCounts' => $orderStatusCounts,
        ]);
    }
}
