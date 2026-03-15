<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class QuoteRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = QuoteRequest::with('client')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('request_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $quoteRequests = $query->paginate(15)->withQueryString();

        // Stats
        $monthStart = now()->startOfMonth();
        $totalThisMonth = QuoteRequest::where('created_at', '>=', $monthStart)->count();
        $pendingCount = QuoteRequest::where('status', 'pending')->count();
        $quotedCount = QuoteRequest::where('status', 'quoted')->where('created_at', '>=', $monthStart)->count();
        $conversionRate = $totalThisMonth > 0 ? round(($quotedCount / $totalThisMonth) * 100, 1) : 0;

        return view('admin.quote-requests.index', compact(
            'quoteRequests', 'totalThisMonth', 'pendingCount', 'quotedCount', 'conversionRate'
        ));
    }

    public function show(QuoteRequest $quoteRequest): View
    {
        $quoteRequest->load('client', 'quote');
        return view('admin.quote-requests.show', compact('quoteRequest'));
    }

    public function updateStatus(Request $request, QuoteRequest $quoteRequest): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,reviewing,quoted,accepted,rejected,expired',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $quoteRequest->update([
            'status' => $request->input('status'),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        return redirect()->route('admin.quote-requests.show', $quoteRequest)->with('success', '狀態已更新');
    }

    public function convertToQuote(QuoteRequest $quoteRequest): RedirectResponse
    {
        if (!in_array($quoteRequest->status, ['pending', 'reviewing'])) {
            return redirect()->back()->with('error', '此報價請求無法轉換');
        }

        $quote = $quoteRequest->convertToQuote();

        return redirect()->route('admin.quotes.show', $quote)->with('success', '已成功轉為正式報價單');
    }
}
