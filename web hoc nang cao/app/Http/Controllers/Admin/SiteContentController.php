<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteContentController extends Controller
{
    public function index(): View
    {
        $siteContents = SiteContent::query()
            ->with('updatedBy')
            ->orderBy('key')
            ->get();

        return view('admin.site-contents.index', compact('siteContents'));
    }

    public function create(): View
    {
        return view('admin.site-contents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'alpha_dash', 'max:255', 'unique:site_contents,key'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        SiteContent::query()->create([
            'key' => $validated['key'],
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'body' => $validated['body'],
            'is_published' => $request->boolean('is_published'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.site-contents.index')->with('status', 'Đã tạo nội dung mới.');
    }

    public function edit(SiteContent $siteContent): View
    {
        return view('admin.site-contents.edit', compact('siteContent'));
    }

    public function update(Request $request, SiteContent $siteContent): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'alpha_dash', 'max:255', Rule::unique('site_contents', 'key')->ignore($siteContent->id)],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $siteContent->update([
            'key' => $validated['key'],
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'body' => $validated['body'],
            'is_published' => $request->boolean('is_published'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.site-contents.index')->with('status', 'Đã cập nhật nội dung.');
    }

    public function destroy(SiteContent $siteContent): RedirectResponse
    {
        $siteContent->delete();

        return redirect()->route('admin.site-contents.index')->with('status', 'Đã xóa nội dung.');
    }
}