<?php

namespace app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BalanceController extends Controller
{
    public function __construct(private readonly BalanceService $balanceService)
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deposit(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|min:1',
                'amount' => 'required|numeric|min:0.01',
                'comment' => 'sometimes|string|max:255',
            ]);

            $result = $this->balanceService->deposit(
                $validated['user_id'],
                $validated['amount'],
                $validated['comment'] ?? null
            );

            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function withdraw(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|min:1',
                'amount' => 'required|numeric|min:0.01',
                'comment' => 'sometimes|string|max:255',
            ]);

            $result = $this->balanceService->withdraw(
                $validated['user_id'],
                $validated['amount'],
                $validated['comment'] ?? null
            );

            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transfer(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from_user_id' => 'required|integer|min:1',
                'to_user_id' => 'required|integer|min:1',
                'amount' => 'required|numeric|min:0.01',
                'comment' => 'sometimes|string|max:255',
            ]);

            $result = $this->balanceService->transfer(
                $validated['from_user_id'],
                $validated['to_user_id'],
                $validated['amount'],
                $validated['comment'] ?? null
            );

            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status);
        }
    }

    /**
     * @param int $userId
     * @return JsonResponse
     */
    public function balance(int $userId): JsonResponse
    {
        try {
            $result = $this->balanceService->getBalance($userId);
            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status);
        }
    }
}
