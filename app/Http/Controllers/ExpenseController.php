<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Models\Outlet;
use App\Services\ReferenceNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);
        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');
        $expenses = Expense::query()->with(['outlet', 'creator'])->forOutlet($outlet)->latest('expense_date')->get();
        return response()->json(ExpenseResource::collection($expenses)->resolve($request));
    }

    public function store(StoreExpenseRequest $request, ReferenceNumberService $references): JsonResponse
    {
        $this->authorize('create', Expense::class);
        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');
        $expense = Expense::query()->create([
            ...$request->validated(),
            'outlet_id' => $outlet->id,
            'created_by' => $request->user()->id,
            'expense_number' => $references->expense($outlet),
            'expense_date' => $request->input('expense_date', now($outlet->timezone)->toDateString()),
        ]);

        return response()->json([
            'message' => 'Pengeluaran berhasil dicatat.',
            'data' => (new ExpenseResource($expense->load(['outlet', 'creator'])))->resolve($request),
        ], 201);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);
        $expense->update($request->validated());
        return response()->json([
            'message' => 'Pengeluaran berhasil diperbarui.',
            'data' => (new ExpenseResource($expense->fresh()->load(['outlet', 'creator'])))->resolve($request),
        ]);
    }

    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return response()->json(['message' => 'Pengeluaran berhasil dihapus.']);
    }
}
