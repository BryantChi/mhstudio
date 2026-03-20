<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactMessage::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $messages = $query->latest()->paginate(15);
        $unreadCount = ContactMessage::unread()->count();

        return view('admin.contact-messages.index', compact('messages', 'unreadCount'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->status === 'unread') {
            $contactMessage->markAsRead();
        }

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:unread,read,replied,archived',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $contactMessage->update($validated);

        if ($validated['status'] === 'replied' && !$contactMessage->replied_at) {
            $contactMessage->update(['replied_at' => now()]);
        }

        flash_success('訊息狀態更新成功');

        return redirect()->route('admin.contact-messages.show', $contactMessage);
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();
        flash_success('訊息已刪除');

        return redirect(admin_list_url('admin.contact-messages.index'));
    }

    public function markAllRead(): RedirectResponse
    {
        ContactMessage::unread()->update([
            'status' => 'read',
            'read_at' => now(),
        ]);

        flash_success('所有訊息已標記為已讀');

        return redirect(admin_list_url('admin.contact-messages.index'));
    }
}
