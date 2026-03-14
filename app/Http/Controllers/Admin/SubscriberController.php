<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subscriber::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscribers = $query->latest()->paginate(15);
        $activeCount = Subscriber::active()->count();

        return view('admin.subscribers.index', compact('subscribers', 'activeCount'));
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();
        flash_success('訂閱者已刪除');

        return redirect()->route('admin.subscribers.index');
    }

    public function export()
    {
        $subscribers = Subscriber::active()->get(['email', 'name', 'subscribed_at']);

        $csv = "Email,Name,Subscribed At\n";
        foreach ($subscribers as $sub) {
            $csv .= "\"{$sub->email}\",\"{$sub->name}\",\"{$sub->subscribed_at}\"\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="subscribers_' . date('Y-m-d') . '.csv"');
    }
}
