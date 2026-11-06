<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Foundation;
use App\Support\SchoolContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FoundationController extends Controller
{
    public function index(SchoolContext $context)
    {
        $foundations = $context->availableFoundations()
            ->loadCount('schools');

        return view('admin.yayasan.index', compact('foundations'));
    }

    public function store(Request $request, SchoolContext $context)
    {
        abort_unless($context->admin()?->isSystemScope(), 403);

        $data = $request->validate([
            'foundation_code' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:core_foundations,foundation_code'],
            'foundation_name' => ['required', 'string', 'max:200'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['foundation_code'] = strtoupper($data['foundation_code']);
        $data['is_active'] = $request->boolean('is_active', true);

        Foundation::create($data);

        return back()->with('success', 'Yayasan berhasil ditambahkan.');
    }

    public function update(Request $request, Foundation $foundation, SchoolContext $context)
    {
        abort_unless($context->availableFoundations()->contains('id', $foundation->id), 403);

        $data = $request->validate([
            'foundation_code' => [
                'required', 'string', 'max:30', 'alpha_dash',
                Rule::unique('core_foundations', 'foundation_code')->ignore($foundation->id),
            ],
            'foundation_name' => ['required', 'string', 'max:200'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['foundation_code'] = strtoupper($data['foundation_code']);
        $data['is_active'] = $request->boolean('is_active');

        $foundation->update($data);

        return back()->with('success', 'Data yayasan berhasil diperbarui.');
    }

    public function destroy(Foundation $foundation, SchoolContext $context)
    {
        abort_unless($context->availableFoundations()->contains('id', $foundation->id), 403);
        abort_unless($context->admin()?->isSystemScope(), 403);

        if ($foundation->schools()->exists()) {
            return back()->with('error', 'Yayasan tidak dapat dihapus karena masih memiliki sekolah.');
        }

        $foundation->delete();

        return back()->with('success', 'Yayasan berhasil dihapus.');
    }
}
