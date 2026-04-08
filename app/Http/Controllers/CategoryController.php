<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:categories',
            'description' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $category = Category::create($validated);
        
        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'string|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $category->update($validated);
        
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        
        return response()->json(['message' => 'Category deleted']);
    }
}
