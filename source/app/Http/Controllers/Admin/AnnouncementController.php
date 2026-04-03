<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::query()
            ->latest()
            ->paginate(12);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'cta_url' => ['nullable', 'url'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Announcement::query()->create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'cta_label' => $validated['cta_label'] ?? null,
            'cta_url' => $validated['cta_url'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.announcements.index')->with('status', 'Đã tạo thông báo mới.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'cta_url' => ['nullable', 'url'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'cta_label' => $validated['cta_label'] ?? null,
            'cta_url' => $validated['cta_url'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.announcements.index')->with('status', 'Đã cập nhật thông báo.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('status', 'Đã xóa thông báo.');
    }
}