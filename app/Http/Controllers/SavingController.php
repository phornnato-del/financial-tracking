<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Saving;

class SavingController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json(['message' => 'Unauthorized - no user', 'error' => 'Please login first'], 401);
            }

            $savings = Saving::where('user_id', $request->user()->id)
                ->with('savingCategory', 'user')
                ->latest()
                ->get();

            return $savings;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Saving index error', 'error' => $e->getMessage()], 500);
        }
    }

    public function activeGoal(){
        
    }

    public function store(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json(['message' => 'Unauthorized - no user', 'error' => 'Please login first'], 401);
            }

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'saving_category_id' => 'required|exists:saving_categories,id',
            ]);

            $saving = Saving::create([
                'user_id' => $request->user()->id,
                ...$validated,
            ]);

 
            $savingCategory = $saving->savingCategory;
            $savingCategory->update([
                'current_amount' => $savingCategory->current_amount + $validated['amount']
            ]);

            return $saving->load('savingCategory', 'user');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Saving store error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, Saving $saving)
    {
        try {
            if ($saving->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return $saving->load('savingCategory', 'user');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Saving show error', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Saving $saving)
    {
        try {
            if ($saving->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'amount' => 'sometimes|numeric|min:0',
                'saving_category_id' => 'sometimes|exists:saving_categories,id',
            ]);


            if (isset($validated['amount']) && $validated['amount'] !== $saving->amount) {
                $difference = $validated['amount'] - $saving->amount;
                $saving->savingCategory->update([
                    'current_amount' => $saving->savingCategory->current_amount + $difference
                ]);
            }

            $saving->update($validated);
            return $saving->load('savingCategory', 'user');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Saving update error', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, Saving $saving)
    {
        try {
            if ($saving->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Subtract the amount from current_amount before deleting
            $saving->savingCategory->update([
                'current_amount' => $saving->savingCategory->current_amount - $saving->amount
            ]);

            $saving->delete();
            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Saving delete error', 'error' => $e->getMessage()], 500);
        }
    }
}
