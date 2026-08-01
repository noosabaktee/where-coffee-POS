<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\OutletResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SettingResource;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Outlet;
use App\Models\OutletSetting;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\MenuPermissionMap;
use App\Services\ReferenceNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class BootstrapController extends Controller
{
    public function __invoke(
        Request $request,
        DashboardService $dashboard,
        ReferenceNumberService $references,
    ): JsonResponse {
        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');
        /** @var User $user */
        $user = $request->user()->load(['outlet', 'roles', 'permissions']);
        $page = (string) $request->query('page', 'dashboard');

        $settings = OutletSetting::query()->firstOrCreate(
            ['outlet_id' => $outlet->id],
            [
                'store_name' => $outlet->name,
                'address' => $outlet->address,
                'phone' => $outlet->phone,
                'timezone' => $outlet->timezone,
            ],
        );

        $products = in_array($page, ['dashboard', 'pos', 'inventori'], true) && $user->can('products.view')
            ? Product::query()->with(['category', 'outlet'])->forOutlet($outlet)->orderBy('name')->get()
            : collect();

        $canLoadCategories = ($page === 'kategori' && $user->can('categories.view'))
            || (in_array($page, ['pos', 'inventori'], true) && $user->can('categories.view'))
            || ($page === 'biaya' && $user->can('expenses.view'));

        $categories = $canLoadCategories
            ? Category::query()
                ->when($page === 'biaya', fn ($query) => $query->ofType(Category::TYPE_EXPENSE))
                ->when(in_array($page, ['pos', 'inventori'], true), fn ($query) => $query->ofType(Category::TYPE_PRODUCT))
                ->orderBy('type')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
            : collect();

        // The POS page searches members on demand through /api/customers/search.
        $customers = $page === 'crm' && $user->can('customers.view')
            ? Customer::query()->active()->orderBy('name')->limit(2000)->get()
            : collect();

        $expenses = $page === 'biaya' && $user->can('expenses.view')
            ? Expense::query()->with(['outlet', 'creator'])->forOutlet($outlet)->latest('expense_date')->limit(1000)->get()
            : collect();

        $transactions = $page === 'laporan' && $user->can('reports.view')
            ? Transaction::query()->with(['items', 'customer', 'user', 'outlet'])->forOutlet($outlet)->latest('transacted_at')->limit(1000)->get()
            : collect();

        $users = $page === 'setting' && $user->can('users.view')
            ? User::query()->with(['outlet', 'roles', 'permissions'])->orderBy('name')->get()
            : collect();

        if ($user->hasRole('Administrator')) {
            $outletQuery = Outlet::query();
            if ($page === 'outlets') {
                $outletQuery
                    ->withCount(['users', 'products', 'transactions'])
                    ->orderByDesc('is_active')
                    ->orderBy('name');
            } else {
                $outletQuery->where('is_active', true)->orderBy('name');
            }
            $outlets = $outletQuery->get();
        } else {
            $outlets = collect([$outlet]);
        }

        $roleMenus = [];
        if ($page === 'setting' && $user->can('settings.view')) {
            foreach (['Kasir', 'Outlet'] as $roleName) {
                $role = Role::query()->where('name', $roleName)->first();
                $permissionNames = $role?->permissions->pluck('name') ?? collect();
                $roleMenus[$roleName] = collect(MenuPermissionMap::all())
                    ->filter(fn (array $menu) => $permissionNames->contains($menu['permissions'][0]))
                    ->keys()
                    ->values()
                    ->all();
            }
        }

        $metrics = in_array($page, ['dashboard', 'analytic'], true)
            && ($user->can('dashboard.view') || $user->can('analytics.view'))
                ? $dashboard->metrics($outlet)
                : [];

        return response()->json([
            'user' => (new UserResource($user))->resolve($request),
            'current_outlet' => (new OutletResource($outlet))->resolve($request),
            'outlets' => OutletResource::collection($outlets)->resolve($request),
            'settings' => (new SettingResource($settings))->resolve($request),
            'products' => ProductResource::collection($products)->resolve($request),
            'categories' => CategoryResource::collection($categories)->resolve($request),
            'customers' => CustomerResource::collection($customers)->resolve($request),
            'expenses' => ExpenseResource::collection($expenses)->resolve($request),
            'transactions' => TransactionResource::collection($transactions)->resolve($request),
            'users' => UserResource::collection($users)->resolve($request),
            'role_menus' => $roleMenus,
            'available_menus' => $page === 'setting' ? MenuPermissionMap::all() : [],
            'metrics' => $metrics,
            'suggested_references' => [
                'product_sku' => $page === 'inventori' && $user->can('products.create')
                    ? $references->productSku($outlet)
                    : null,
                'expense_number' => $page === 'biaya' && $user->can('expenses.create')
                    ? $references->expense($outlet)
                    : null,
            ],
        ]);
    }
}
