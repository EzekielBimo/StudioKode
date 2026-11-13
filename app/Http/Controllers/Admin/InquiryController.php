<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InquiryController extends Controller
{
    /**
     * Display a listing of client consultation inquiries with filtering and search.
     */
    public function index(Request $request): Response
    {
        $query = Inquiry::query();

        // Status filtering supporting Indonesian and English aliases
        if ($request->filled('status')) {
            $status = strtolower(trim((string) $request->query('status')));
            if (in_array($status, ['baru', 'new'], true)) {
                $query->whereIn('status', ['new', 'baru']);
            } elseif (in_array($status, ['dihubungi', 'contacted'], true)) {
                $query->whereIn('status', ['contacted', 'dihubungi']);
            } elseif (in_array($status, ['diarsipkan', 'archived'], true)) {
                $query->whereIn('status', ['archived', 'diarsipkan']);
            } elseif (!in_array($status, ['all', 'semua', ''], true)) {
                $query->where('status', $status);
            }
        }

        // Full-text search across client attributes
        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('site_location', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Order latest inquiries first
        $inquiries = $query->latest()->paginate(15)->withQueryString();

        // Calculate status counts efficiently in a single query
        $counts = Inquiry::query()
            ->selectRaw("
                COUNT(*) as count_all,
                SUM(CASE WHEN status IN ('new', 'baru') THEN 1 ELSE 0 END) as count_baru,
                SUM(CASE WHEN status IN ('contacted', 'dihubungi') THEN 1 ELSE 0 END) as count_dihubungi,
                SUM(CASE WHEN status IN ('archived', 'diarsipkan') THEN 1 ELSE 0 END) as count_diarsipkan
            ")
            ->first();

        $statusCounts = [
            'all' => (int) ($counts->count_all ?? 0),
            'baru' => (int) ($counts->count_baru ?? 0),
            'dihubungi' => (int) ($counts->count_dihubungi ?? 0),
            'diarsipkan' => (int) ($counts->count_diarsipkan ?? 0),
        ];

        return Inertia::render('Admin/Inquiries/Index', [
            'inquiries' => $inquiries,
            'filters' => [
                'status' => $request->query('status', ''),
                'search' => $request->query('search', ''),
            ],
            'status_counts' => $statusCounts,
        ]);
    }

    /**
     * Display the specified consultation inquiry details.
     */
    public function show(Inquiry $inquiry): Response
    {
        return Inertia::render('Admin/Inquiries/Show', [
            'inquiry' => $inquiry,
        ]);
    }

    /**
     * Update the status of the specified consultation inquiry.
     */
    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:new,baru,contacted,dihubungi,archived,diarsipkan',
            ],
        ], [
            'status.required' => 'Status konsultasi wajib dipilih.',
            'status.in' => 'Status konsultasi yang dipilih tidak valid.',
        ]);

        $inquiry->status = $validated['status'];
        $inquiry->save();

        return back()->with('success', 'Status pesan konsultasi berhasil diperbarui.');
    }

    /**
     * Remove the specified consultation inquiry from storage.
     */
    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')
            ->with('success', 'Pesan konsultasi berhasil dihapus.');
    }
}
