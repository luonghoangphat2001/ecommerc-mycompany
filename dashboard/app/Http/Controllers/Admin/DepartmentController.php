<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('agents')->get();

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.form', ['department' => new Department]);
    }

    public function store(DepartmentRequest $request)
    {
        $validated = $request->validated();

        Department::create($validated);

        return $this->redirectToIndex('Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->load('agents');

        return view('admin.departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        return view('admin.departments.form', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $validated = $request->validated();

        $department->update($validated);

        return $this->redirectToIndex('Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return $this->redirectToIndex('Department deleted successfully.');
    }

    private function redirectToIndex(string $message): RedirectResponse
    {
        return redirect()->route('admin.departments.index')->with('success', $message);
    }
}
