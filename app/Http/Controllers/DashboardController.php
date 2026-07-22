<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardPeriodRequest;
use App\Models\Outlet;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(DashboardPeriodRequest $request, DashboardService $dashboard): JsonResponse
    {
        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');

        return response()->json($dashboard->metrics(
            $outlet,
            $request->validated('from'),
            $request->validated('to'),
        ));
    }
}
