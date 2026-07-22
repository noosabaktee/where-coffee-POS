<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function dashboard(Request $request): View
    {
        return $this->page($request, 'pages.dashboard', 'dashboard', 'Dashboard', 'menu.dashboard');
    }

    public function analytics(Request $request): View
    {
        return $this->page($request, 'pages.analytics', 'analytic', 'Analisis Bisnis', 'menu.analytic');
    }

    public function pos(Request $request): View
    {
        return $this->page($request, 'pages.pos', 'pos', 'Sistem Kasir', 'menu.pos');
    }

    public function inventory(Request $request): View
    {
        return $this->page($request, 'pages.inventory', 'inventori', 'Manajemen Stok', 'menu.inventori');
    }

    public function reports(Request $request): View
    {
        return $this->page($request, 'pages.reports', 'laporan', 'Keuangan & Laporan', 'menu.laporan');
    }

    public function expenses(Request $request): View
    {
        return $this->page($request, 'pages.expenses', 'biaya', 'Biaya Operasional', 'menu.biaya');
    }

    public function categories(Request $request): View
    {
        return $this->page($request, 'pages.categories', 'kategori', 'Master Kategori', 'menu.kategori');
    }

    public function crm(Request $request): View
    {
        return $this->page($request, 'pages.crm', 'crm', 'Manajemen CRM', 'menu.crm');
    }

    public function settings(Request $request): View
    {
        return $this->page($request, 'pages.settings', 'setting', 'Pengaturan Toko', 'menu.setting');
    }

    public function outlets(Request $request): View
    {
        abort_unless($request->user()->hasRole('Administrator'), 403);

        return view('pages.outlets', [
            'pageId' => 'outlets',
            'pageTitle' => 'Pengelolaan Cabang',
        ]);
    }

    private function page(
        Request $request,
        string $view,
        string $pageId,
        string $pageTitle,
        string $permission,
    ): View {
        $user = $request->user();
        abort_unless($user->hasRole('Administrator') || $user->can($permission), 403);

        return view($view, compact('pageId', 'pageTitle'));
    }
}
