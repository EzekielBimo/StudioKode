<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the administrative dashboard with KPI metrics and recent activity.
     */
    public function index(): Response
    {
        // Compute Project statistics efficiently in a single query
        $projectStats = Project::query()
            ->selectRaw("
                COUNT(*) as total_projects,
                SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_projects,
                SUM(CASE WHEN published_at IS NOT NULL AND published_at <= ? THEN 1 ELSE 0 END) as published_projects
            ", [now()])
            ->first();

        // Compute Inquiry statistics efficiently in a single query
        $inquiryStats = Inquiry::query()
            ->selectRaw("
                COUNT(*) as total_inquiries,
                SUM(CASE WHEN status IN ('new', 'baru') THEN 1 ELSE 0 END) as new_inquiries
            ")
            ->first();

        $metrics = [
            'total_projects' => (int) ($projectStats->total_projects ?? 0),
            'featured_projects' => (int) ($projectStats->featured_projects ?? 0),
            'published_projects' => (int) ($projectStats->published_projects ?? 0),
            'total_inquiries' => (int) ($inquiryStats->total_inquiries ?? 0),
            'new_inquiries' => (int) ($inquiryStats->new_inquiries ?? 0),
        ];

        // 5 most recent projects
        $recentProjects = Project::query()
            ->latest()
            ->take(5)
            ->get(['id', 'title', 'slug', 'location', 'is_featured', 'published_at', 'created_at']);

        // 5 most recent inquiries
        $recentInquiries = Inquiry::query()
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'site_location', 'status', 'created_at']);

        return Inertia::render('Admin/Dashboard/Index', [
            'metrics' => $metrics,
            'recent_projects' => $recentProjects,
            'recent_inquiries' => $recentInquiries,
        ]);
    }
}
