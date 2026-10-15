<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface;
use App\Models\Department;
use App\Models\DepartmentAgent;
use App\Models\DepartmentCustomerReview;
use App\Models\DepartmentEmployeeContract;
use App\Models\DepartmentFinancialProposal;
use App\Models\DepartmentIncident;
use App\Models\DepartmentMaterialPrice;
use App\Models\DepartmentPurchaseOrder;
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
    public function __construct(private readonly AnalyticsServiceInterface $analytics)
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->hasRole('super_admin')) {
                return $next($request);
            }
            try {
                if (!auth()->user()->hasPermissionTo('view_dashboard')) {
                    abort(403, 'Unauthorized action. Missing permission: view_dashboard');
                }
            } catch (\Exception $e) {
                abort(403, 'Unauthorized action. Missing permission: view_dashboard');
            }
            return $next($request);
        });
    }

    public function __invoke(): View
    {
        $latestOrders = Order::latest('id')->take(6)->get(['id', 'number', 'status', 'total', 'currency', 'created_at']);
        $orderStatusCounts = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('total', 'status');
        $locale = app()->getLocale();
        $revenueSeries = $this->analytics->getRevenueChartData('current');
        $topProducts = $this->analytics->getTopProductsChartData(8, $locale);
        $topProductsDonut = $this->analytics->getTopProductsDonutChartData(5, $locale);
        $orderDistribution = $this->analytics->getOrderStatusDistribution();

        // DAN ESR Executive Center Data
        $swarmAgents = DepartmentAgent::with('department')->get();
        $pendingProposals = DepartmentFinancialProposal::with(['department', 'user'])
            ->where('status', 'pending')
            ->orderByDesc('is_urgent')
            ->latest('id')
            ->take(5)
            ->get();
        $urgentIncidents = DepartmentIncident::with(['department', 'order'])
            ->whereIn('status', ['open', 'investigating'])
            ->latest('id')
            ->take(5)
            ->get();
        $pendingPurchaseOrders = DepartmentPurchaseOrder::with('department')
            ->whereIn('status', ['pending', 'shipping', 'partial'])
            ->latest('id')
            ->take(5)
            ->get();
        $materialPrices = DepartmentMaterialPrice::latest('id')->take(5)->get();
        $expiringContractsCount = DepartmentEmployeeContract::where('end_date', '<=', now()->addDays(30))->count();
        $negativeReviewsCount = DepartmentCustomerReview::where('rating', '<=', 2)->count();
        $lowStockProductsCount = Product::where('stock', '<=', 10)->count();
        $urgentProposalsCount = DepartmentFinancialProposal::where('status', 'pending')->where('is_urgent', true)->count();
        $departments = Department::where('is_active', true)->get();

        return view('admin.dashboard', [
            'title' => __('admin.dashboard.title'),
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
            'revenueSeries' => $revenueSeries,
            'topProducts' => $topProducts,
            'topProductsDonut' => $topProductsDonut,
            'orderDistribution' => $orderDistribution,

            // DAN ESR Executive Command Center
            'swarmAgents' => $swarmAgents,
            'pendingProposals' => $pendingProposals,
            'urgentIncidents' => $urgentIncidents,
            'pendingPurchaseOrders' => $pendingPurchaseOrders,
            'materialPrices' => $materialPrices,
            'expiringContractsCount' => $expiringContractsCount,
            'negativeReviewsCount' => $negativeReviewsCount,
            'lowStockProductsCount' => $lowStockProductsCount,
            'urgentProposalsCount' => $urgentProposalsCount,
            'departments' => $departments,
        ]);
    }
}
