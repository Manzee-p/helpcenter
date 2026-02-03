<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketCategoryController extends Controller
{
    /**
     * Get all categories
     */
    public function index(Request $request)
    {
        $query = TicketCategory::withCount('tickets');

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $categories = $query->orderBy('name', 'asc')->get();

        return response()->json($categories);
    }

    /**
     * Create new category (Admin only)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_categories',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Generate slug dari name
        $validated['slug'] = Str::slug($validated['name']);

        $category = TicketCategory::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Get single category
     */
    public function show($id)
    {
        $category = TicketCategory::withCount('tickets')->findOrFail($id);

        return response()->json($category);
    }

    /**
     * Update category (Admin only)
     */
    public function update(Request $request, $id)
    {
        $category = TicketCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:ticket_categories,name,' . $id,
            'description' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // Update slug jika name berubah
        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    /**
     * Delete category (Admin only)
     */
    public function destroy($id)
    {
        $category = TicketCategory::findOrFail($id);

        // Check if category has tickets
        if ($category->tickets()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with existing tickets. Please reassign tickets first.'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Get category statistics
     */
    public function statistics()
    {
        $categories = TicketCategory::withCount([
            'tickets',
            'tickets as new_tickets_count' => function($query) {
                $query->where('status', 'new');
            },
            'tickets as resolved_tickets_count' => function($query) {
                $query->whereIn('status', ['resolved', 'closed']);
            }
        ])->get();

        return response()->json($categories);
    }
}