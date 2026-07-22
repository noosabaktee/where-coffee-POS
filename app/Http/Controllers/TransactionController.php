<?php

namespace App\Http\Controllers;

use App\Exports\TransactionReportExcel;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Transaction::class);
        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');
        $transactions = Transaction::query()
            ->with(['items', 'customer', 'user', 'outlet'])
            ->forOutlet($outlet)
            ->when($request->filled('from'), fn ($query) => $query->whereDate('transacted_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('transacted_at', '<=', $request->date('to')))
            ->latest('transacted_at')
            ->limit(2000)
            ->get();

        return response()->json(TransactionResource::collection($transactions)->resolve($request));
    }

    public function store(CheckoutRequest $request, CheckoutService $checkout): JsonResponse
    {
        $this->authorize('create', Transaction::class);
        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');
        $transaction = $checkout->checkout($request->user(), $outlet, $request->validated());

        return response()->json([
            'message' => 'Pembayaran berhasil diproses.',
            'data' => (new TransactionResource($transaction))->resolve($request),
        ], 201);
    }

    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        $this->authorize('view', $transaction);
        return response()->json((new TransactionResource($transaction->load(['items', 'customer', 'user', 'outlet'])))->resolve($request));
    }

    public function export(Request $request, TransactionReportExcel $report): StreamedResponse
    {
        abort_unless($request->user()->can('reports.export'), 403);

        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');
        $filename = 'where-coffee-'.str($outlet->code)->slug().'-'.now($outlet->timezone)->format('Ymd-His').'.xlsx';
        $spreadsheet = $report->build($outlet);

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
        ]);
    }
}
