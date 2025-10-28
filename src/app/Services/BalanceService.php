<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BalanceService
{
    /**
     * @param int $userId
     * @param float $amount
     * @param string|null $comment
     * @return array
     */
    public function deposit(int $userId, float $amount, string $comment = null): array
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::find($userId);
            if (!$user) {
                throw ValidationException::withMessages([
                    'user_id' => 'User not found',
                ])->status(404);
            }

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Amount must be greater than 0',
                ])->status(422);
            }

            $balance = Balance::firstOrCreate(
                ['user_id' => $userId],
                ['amount' => 0]
            );

            $balance->increment('amount', $amount);

            Transaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            return [
                'user_id' => $userId,
                'balance' => (float) $balance->fresh()->amount,
                'message' => 'Deposit successful',
            ];
        });
    }

    /**
     * @param int $userId
     * @param float $amount
     * @param string|null $comment
     * @return array
     */
    public function withdraw(int $userId, float $amount, string $comment = null): array
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::find($userId);
            if (!$user) {
                throw ValidationException::withMessages([
                    'user_id' => 'User not found',
                ])->status(404);
            }

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Amount must be greater than 0',
                ])->status(422);
            }

            $balance = Balance::where('user_id', $userId)->first();

            if (!$balance || $balance->amount < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient funds',
                ])->status(409);
            }

            $balance->decrement('amount', $amount);

            Transaction::create([
                'user_id' => $userId,
                'type' => 'withdraw',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            return [
                'user_id' => $userId,
                'balance' => (float) $balance->fresh()->amount,
                'message' => 'Withdrawal successful',
            ];
        });
    }

    /**
     * @param int $fromUserId
     * @param int $toUserId
     * @param float $amount
     * @param string|null $comment
     * @return array
     */
    public function transfer(int $fromUserId, int $toUserId, float $amount, string $comment = null): array
    {
        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $comment) {
            if ($fromUserId === $toUserId) {
                throw ValidationException::withMessages([
                    'to_user_id' => 'Cannot transfer to same user',
                ])->status(422);
            }

            $fromUser = User::find($fromUserId);
            $toUser = User::find($toUserId);

            if (!$fromUser) {
                throw ValidationException::withMessages([
                    'from_user_id' => 'From user not found',
                ])->status(404);
            }

            if (!$toUser) {
                throw ValidationException::withMessages([
                    'to_user_id' => 'To user not found',
                ])->status(404);
            }

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Amount must be greater than 0',
                ])->status(422);
            }

            $fromBalance = Balance::where('user_id', $fromUserId)->first();
            if (!$fromBalance || $fromBalance->amount < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient funds',
                ])->status(409);
            }

            $toBalance = Balance::firstOrCreate(
                ['user_id' => $toUserId],
                ['amount' => 0]
            );

            $fromBalance->decrement('amount', $amount);
            $toBalance->increment('amount', $amount);

            Transaction::create([
                'user_id' => $fromUserId,
                'type' => 'transfer_out',
                'amount' => $amount,
                'comment' => $comment,
                'related_user_id' => $toUserId,
            ]);

            Transaction::create([
                'user_id' => $toUserId,
                'type' => 'transfer_in',
                'amount' => $amount,
                'comment' => $comment,
                'related_user_id' => $fromUserId,
            ]);

            return [
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'amount' => $amount,
                'from_user_balance' => (float) $fromBalance->fresh()->amount,
                'to_user_balance' => (float) $toBalance->fresh()->amount,
                'message' => 'Transfer successful',
            ];
        });
    }

    /**
     * @param int $userId
     * @return array
     * @throws ValidationException
     */
    public function getBalance(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => 'User not found',
            ])->status(404);
        }

        $balance = Balance::where('user_id', $userId)->first();
        $currentBalance = $balance ? (float) $balance->amount : 0.0;

        return [
            'user_id' => $userId,
            'balance' => $currentBalance,
        ];
    }
}
