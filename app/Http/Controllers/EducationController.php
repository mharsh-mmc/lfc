<?php

namespace App\Http\Controllers;

use App\Models\Education;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'institution' => 'required|string|max:255',
            'degree' => 'nullable|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'period' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_current' => 'boolean',
        ]);

        // If this entry is being marked as current, unmark all other entries
        if ($request->boolean('is_current')) {
            Auth::user()->education()->update(['is_current' => false]);
        }

        Auth::user()->education()->create([
            'institution' => $request->institution,
            'degree' => $request->degree,
            'field_of_study' => $request->field_of_study,
            'period' => $request->period,
            'description' => $request->description,
            'is_current' => $request->boolean('is_current'),
            'order' => Auth::user()->education()->count(),
        ]);

        return back()->with('success', 'Education entry added successfully.');
    }

    public function update(Request $request, Education $education)
    {
        $this->authorize('update', $education);

        $request->validate([
            'institution' => 'required|string|max:255',
            'degree' => 'nullable|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'period' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_current' => 'boolean',
        ]);

        // If this entry is being marked as current, unmark all other entries
        if ($request->boolean('is_current')) {
            Auth::user()->education()
                ->where('id', '!=', $education->id)
                ->update(['is_current' => false]);
        }

        // Update all fields, allowing null values for optional fields
        $updateData = [
            'institution' => $request->institution,
            'degree' => $request->degree,
            'field_of_study' => $request->field_of_study,
            'period' => $request->period,
            'description' => $request->description,
            'is_current' => $request->boolean('is_current'),
        ];

        $education->update($updateData);

        return back()->with('success', 'Education entry updated successfully.');
    }

    public function destroy(Education $education)
    {
        $this->authorize('delete', $education);

        $education->delete();

        return back()->with('success', 'Education entry deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'education_ids' => 'required|array',
            'education_ids.*' => 'integer|exists:education,id',
        ]);

        foreach ($request->education_ids as $index => $id) {
            Education::where('id', $id)
                ->where('user_id', Auth::id())
                ->update(['order' => $index]);
        }

        return back()->with('success', 'Education entries reordered successfully.');
    }
}
