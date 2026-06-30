<?php

namespace App\Http\Controllers;

use App\Models\SavingCategory;
use Illuminate\Http\Request;

class SavingCategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json(['message' => 'Unauthorized - no user', 'error' => 'Please login first'], 401);
            }

            return SavingCategory::where('user_id', $request->user()->id)
                ->with('savings', 'user')
                ->latest()
                ->get();
        } catch (\Exception $e) {
            return response()->json(['message' => 'SavingCategory index error', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json(['message' => 'Unauthorized - no user', 'error' => 'Please login first'], 401);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'goal_amount' => 'required|numeric|min:0',
                'current_amount' => 'nullable|numeric|min:0',
                'duration' => 'nullable|integer|min:1',
                'unit' => 'nullable|string',
                'frequency' => 'nullable|string|in:Daily,Weekly,Monthly,Quarterly,Yearly',
                'purpose_id' => 'nullable|exists:purpose,id',
            ]);

            return SavingCategory::create([
                'user_id' => $request->user()->id,
                ...$validated,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'SavingCategory store error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, SavingCategory $savingCategory)
    {
        try {
            if ($savingCategory->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return $savingCategory->load('savings', 'user');
        } catch (\Exception $e) {
            return response()->json(['message' => 'SavingCategory show error', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, SavingCategory $savingCategory)
    {
        try {
            if ($savingCategory->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'goal_amount' => 'sometimes|numeric|min:0',
                'current_amount' => 'sometimes|numeric|min:0',
                'duration' => 'sometimes|integer|min:1',
                'unit' => 'sometimes|string|in:Days,Weeks,Months,Years',
                'frequency' => 'sometimes|string|in:Daily,Weekly,Monthly,Quarterly,Yearly',
                'purpose_id' => 'nullable|exists:purpose,id',
            ]);

            $savingCategory->update($validated);
            return $savingCategory->load('savings', 'user');
        } catch (\Exception $e) {
            return response()->json(['message' => 'SavingCategory update error', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, SavingCategory $savingCategory)
    {
        try {
            if ($savingCategory->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $savingCategory->delete();
            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json(['message' => 'SavingCategory delete error', 'error' => $e->getMessage()], 500);
        }
    }
}
