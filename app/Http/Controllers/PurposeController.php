<?php

namespace App\Http\Controllers;

use App\Models\Purpose;
use App\Models\SavingCategory;
use Illuminate\Http\Request;

class PurposeController extends Controller
{
    public function index(Request $request)
    {
        return Purpose::latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        return Purpose::create($validated);
    }

    public function show(Purpose $purpose)
    {
        return $purpose->load('savingCategories');
    }

    public function update(Request $request, Purpose $purpose)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255,' . $purpose->id,
        ]);

        $purpose->update($validated);
        return $purpose;
    }

    public function destroy(Purpose $purpose)
    {
        $purpose->delete();
        return response()->noContent();
    }

    public function activeGoal(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json(['message' => 'Unauthorized - no user', 'error' => 'Please login first'], 401);
            }

            // Get all saving categories for the user with goal_amount > 0
            $categories = SavingCategory::where('user_id', $request->user()->id)
                ->where('goal_amount', '>', 0)
                ->with('purpose')
                ->get();

            $activeGoals = [];
            
            foreach ($categories as $category) {
                $progress = $category->goal_amount > 0 
                    ? round(($category->current_amount / $category->goal_amount) * 100, 2)
                    : 0;

                $activeGoals[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'user_id' => $category->user_id,
                    'current_amount' => (float) $category->current_amount,
                    'goal_amount' => (float) $category->goal_amount,
                    'progress_percentage' => $progress,
                    'remaining_amount' => (float) ($category->goal_amount - $category->current_amount),
                    'duration' => $category->duration,
                    'unit' => $category->unit,
                    'frequency' => $category->frequency,
                ];
            }

            usort($activeGoals, function ($a, $b) {
                return $b['progress_percentage'] <=> $a['progress_percentage'];
            });

            return response()->json([
                'data' => $activeGoals,
                'count' => count($activeGoals)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Active goal error', 'error' => $e->getMessage()], 500);
        }
    }
}
