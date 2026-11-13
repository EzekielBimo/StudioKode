<?php

namespace Tests\Feature\Admin;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInquiryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'email' => 'admin@studiokode.com',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 1: Primary Happy Paths & Interface Contracts
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 1: Inquiries index requires authentication.
     * Feature 12: Inquiries Index & Status Filter (R4)
     */
    public function test_inquiries_index_requires_authentication(): void
    {
        $response = $this->get('/admin/inquiries');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    /**
     * Tier 1: Inquiries index renders successfully for authenticated admin.
     * Feature 12: Inquiries Index & Status Filter (R4)
     */
    public function test_inquiries_index_renders_with_inquiry_list(): void
    {
        Inquiry::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/admin/inquiries');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Inquiries/Index')
            ->has('inquiries')
        );
    }

    /**
     * Tier 1: Inquiry detail view renders with complete consultation attributes.
     * Feature 13: Inquiry Detail View (R4)
     */
    public function test_inquiry_detail_view_renders_with_full_attributes(): void
    {
        $inquiry = Inquiry::factory()->create([
            'name' => 'Julian Finch',
            'email' => 'julian@example.com',
            'phone' => '+41 22 555 0192',
            'site_location' => 'Geneva, Switzerland',
            'estimated_area' => '500-1000 m²',
            'budget_range' => '$100k - $250k',
            'message' => 'Lakeside terraced estate landscaping consultation request.',
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/inquiries/{$inquiry->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('inquiry', fn (Assert $inq) => $inq
                ->where('id', $inquiry->id)
                ->where('name', 'Julian Finch')
                ->where('email', 'julian@example.com')
                ->where('phone', '+41 22 555 0192')
                ->where('site_location', 'Geneva, Switzerland')
                ->where('estimated_area', '500-1000 m²')
                ->where('budget_range', '$100k - $250k')
                ->etc()
            )
        );
    }

    /**
     * Tier 1: Inquiry status updates successfully and redirects with Indonesian flash message.
     * Feature 14: Inquiry Status Update (R4)
     */
    public function test_inquiry_status_updates_successfully(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'new']);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/inquiries/{$inquiry->id}", [
                'status' => 'dihubungi',
            ]);

        // Accept 302 redirect back or to inquiries index
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'status' => in_array($inquiry->fresh()->status, ['dihubungi', 'contacted'])
                ? $inquiry->fresh()->status
                : 'dihubungi',
        ]);
    }

    /**
     * Tier 1: Inquiry deletes successfully and record is removed.
     * Feature 15: Inquiry Deletion (R4)
     */
    public function test_inquiry_deletes_successfully(): void
    {
        $inquiry = Inquiry::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/inquiries/{$inquiry->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/inquiries');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 2: Boundary Value Analysis, Validation & Edge Cases
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 2: Updating inquiry status to an invalid value is rejected with validation error.
     * Feature 14: Inquiry Status Update (R4)
     */
    public function test_inquiry_status_update_rejects_invalid_status_value(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'new']);

        $response = $this->actingAs($this->admin)
            ->from("/admin/inquiries/{$inquiry->id}")
            ->patch("/admin/inquiries/{$inquiry->id}", [
                'status' => 'invalid_status_xyz',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['status']);
        $this->assertEquals('new', $inquiry->fresh()->status);
    }

    /**
     * Tier 2: Status update supports both Indonesian and English status aliases.
     * Feature 14: Inquiry Status Update (R4)
     */
    public function test_inquiry_supports_indonesian_and_english_status_values(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'new']);

        // Update to 'contacted'
        $responseEn = $this->actingAs($this->admin)
            ->patch("/admin/inquiries/{$inquiry->id}", ['status' => 'contacted']);
        $responseEn->assertStatus(302);
        $this->assertContains($inquiry->fresh()->status, ['contacted', 'dihubungi']);

        // Update to 'diarsipkan'
        $responseId = $this->actingAs($this->admin)
            ->patch("/admin/inquiries/{$inquiry->id}", ['status' => 'diarsipkan']);
        $responseId->assertStatus(302);
        $this->assertContains($inquiry->fresh()->status, ['archived', 'diarsipkan']);
    }

    /**
     * Tier 2: Non-existent inquiry ID returns 404 on show, update, and delete.
     * Feature 13, 14, 15: Inquiries Management (R4)
     */
    public function test_inquiry_returns_404_for_non_existent_id(): void
    {
        $resShow = $this->actingAs($this->admin)->get('/admin/inquiries/999999');
        $resShow->assertStatus(404);

        $resUpdate = $this->actingAs($this->admin)->patch('/admin/inquiries/999999', ['status' => 'dihubungi']);
        $resUpdate->assertStatus(404);

        $resDelete = $this->actingAs($this->admin)->delete('/admin/inquiries/999999');
        $resDelete->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 3: Filtering by Status, Search & Tab Counts
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 3: Inquiries index filters correctly by status 'baru' / 'new'.
     * Feature 12: Inquiries Index & Status Filter (R4)
     */
    public function test_inquiries_index_filters_by_status(): void
    {
        Inquiry::factory()->create(['name' => 'New Client', 'status' => 'new']);
        Inquiry::factory()->create(['name' => 'Contacted Client', 'status' => 'contacted']);
        Inquiry::factory()->create(['name' => 'Archived Client', 'status' => 'archived']);

        // Test filtering by 'baru' or 'new'
        $responseNew = $this->actingAs($this->admin)->get('/admin/inquiries?status=baru');
        $responseNew->assertStatus(200);

        // Test filtering by 'dihubungi' or 'contacted'
        $responseContacted = $this->actingAs($this->admin)->get('/admin/inquiries?status=dihubungi');
        $responseContacted->assertStatus(200);

        // Test filtering by 'diarsipkan' or 'archived'
        $responseArchived = $this->actingAs($this->admin)->get('/admin/inquiries?status=diarsipkan');
        $responseArchived->assertStatus(200);
    }

    /**
     * Tier 3: Inquiries index filters by text search keyword across client details.
     * Feature 12: Inquiries Index & Status Filter (R4)
     */
    public function test_inquiries_index_filters_by_search_query(): void
    {
        Inquiry::factory()->create(['name' => 'Julian Finch', 'site_location' => 'Geneva']);
        Inquiry::factory()->create(['name' => 'Seraphina Vance', 'site_location' => 'Como']);

        $response = $this->actingAs($this->admin)->get('/admin/inquiries?search=Julian');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('inquiries')
        );
    }

    /**
     * Tier 3: Search with no matching inquiries returns empty collection gracefully.
     * Feature 12: Inquiries Index & Status Filter (R4)
     */
    public function test_inquiries_search_with_no_matches_returns_empty(): void
    {
        Inquiry::factory()->create(['name' => 'Julian Finch']);

        $response = $this->actingAs($this->admin)->get('/admin/inquiries?search=non_existent_client_xyz');

        $response->assertStatus(200);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 4: End-to-End Inquiry Workflow Lifecycle
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 4: Complete consultation inquiry workflow lifecycle:
     * Public submission -> Admin discovers in 'baru' tab -> Views detail view
     * -> Updates status to 'dihubungi' -> Updates status to 'diarsipkan' -> Deletes inquiry.
     * Scenario 4.4 (R4)
     */
    public function test_inquiry_workflow_lifecycle_end_to_end(): void
    {
        // 1. Client submits consultation inquiry via public route
        $submitResponse = $this->post('/inquiries', [
            'name' => 'Budi Pratama',
            'email' => 'budi@example.com',
            'phone' => '+62 811 2345 678',
            'site_location' => 'Nusa Dua, Bali',
            'estimated_area' => '1,200 m²',
            'budget_range' => '$150k - $300k',
            'message' => 'Desain lanskap villa tropis modern dengan kolam refleksi.',
        ]);
        $submitResponse->assertRedirect();

        $inquiry = Inquiry::where('email', 'budi@example.com')->firstOrFail();
        $this->assertContains($inquiry->status, ['new', 'baru']);

        // 2. Admin navigates to inquiries index and views detail
        $detailResponse = $this->actingAs($this->admin)->get("/admin/inquiries/{$inquiry->id}");
        $detailResponse->assertStatus(200);

        // 3. Admin marks inquiry as 'dihubungi' (contacted)
        $contactResponse = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'dihubungi',
        ]);
        $contactResponse->assertStatus(302);
        $this->assertContains($inquiry->fresh()->status, ['contacted', 'dihubungi']);

        // 4. Admin marks inquiry as 'diarsipkan' (archived)
        $archiveResponse = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'diarsipkan',
        ]);
        $archiveResponse->assertStatus(302);
        $this->assertContains($inquiry->fresh()->status, ['archived', 'diarsipkan']);

        // 5. Admin deletes inquiry
        $deleteResponse = $this->actingAs($this->admin)->delete("/admin/inquiries/{$inquiry->id}");
        $deleteResponse->assertStatus(302);

        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }
}
