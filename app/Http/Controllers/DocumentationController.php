<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentationController extends Controller
{
    public function index()
    {
        $docs = Documentation::with('author')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if (request()->wantsJson()) {
            return response()->json($docs);
        }

        return Inertia::render('Documentation/Index', [
            'records' => $docs,
            'options' => [
                'types' => Documentation::getTypes(),
                'statuses' => Documentation::getStatuses()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|json', // Validates that content is valid JSON
            'type' => 'required|string',
            'status' => 'required|string',
            'tags' => 'nullable|array'
        ]);

        $validated['author_id'] = auth()->id();

        $documentation = Documentation::create($validated);

        return response()->json([
            'message' => 'Documentation created successfully',
            'record' => $documentation
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|json', // Validates that content is valid JSON
            'type' => 'required|string',
            'status' => 'required|string',
            'tags' => 'nullable|string'
        ]);

        $documentation = Documentation::findOrFail($id);
        $documentation->update($validated);

        return response()->json([
            'message' => 'Documentation updated successfully',
            'record' => $documentation
        ]);
    }

    public function destroy($id)
    {
        $documentation = Documentation::findOrFail($id);
        $documentation->delete();

        return response()->json([
            'message' => 'Documentation deleted successfully'
        ]);
    }
}









