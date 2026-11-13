<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengerM2_1_InquiryStressTest extends TestCase
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
    | Dimension 1: Status Transitions State Machine & HTTP Method Matrix
    |--------------------------------------------------------------------------
    */

    /**
     * Test complete cyclic status transition:
     * baru -> dihubungi -> diarsipkan -> baru (re-opening consultation).
     */
    public function test_inquiry_full_cycle_status_transitions(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'baru']);

        // 1. Move baru -> dihubungi (using PATCH)
        $res1 = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'dihubungi',
        ]);
        $res1->assertStatus(302);
        $res1->assertSessionHas('success', 'Status pesan konsultasi berhasil diperbarui.');
        $this->assertEquals('dihubungi', $inquiry->fresh()->status);

        // 2. Move dihubungi -> diarsipkan (using PUT)
        $res2 = $this->actingAs($this->admin)->put("/admin/inquiries/{$inquiry->id}", [
            'status' => 'diarsipkan',
        ]);
        $res2->assertStatus(302);
        $res2->assertSessionHas('success', 'Status pesan konsultasi berhasil diperbarui.');
        $this->assertEquals('diarsipkan', $inquiry->fresh()->status);

        // 3. Re-open: diarsipkan -> baru (using PATCH)
        $res3 = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'baru',
        ]);
        $res3->assertStatus(302);
        $res3->assertSessionHas('success', 'Status pesan konsultasi berhasil diperbarui.');
        $this->assertEquals('baru', $inquiry->fresh()->status);
    }

    /**
     * Test direct status leaps and English alias equivalents.
     */
    public function test_inquiry_status_direct_leaps_and_english_aliases(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'new']);

        // Direct leap: new -> archived
        $res1 = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'archived',
        ]);
        $res1->assertStatus(302);
        $this->assertEquals('archived', $inquiry->fresh()->status);

        // Direct leap: archived -> contacted
        $res2 = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'contacted',
        ]);
        $res2->assertStatus(302);
        $this->assertEquals('contacted', $inquiry->fresh()->status);

        // Direct leap: contacted -> new
        $res3 = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'new',
        ]);
        $res3->assertStatus(302);
        $this->assertEquals('new', $inquiry->fresh()->status);
    }

    /**
     * Test idempotent status update (updating to same status).
     */
    public function test_inquiry_status_update_is_idempotent(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'baru']);

        $response = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'baru',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertEquals('baru', $inquiry->fresh()->status);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 2: Malformed Payloads & Status Validation Hardening
    |--------------------------------------------------------------------------
    */

    /**
     * Test status update rejects invalid strings, numbers, arrays, and missing status.
     */
    public function test_inquiry_status_rejects_malformed_and_unauthorized_values(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'baru']);

        $invalidPayloads = [
            'pending_status'   => ['status' => 'pending'],
            'completed_status' => ['status' => 'completed'],
            'spam_status'      => ['status' => 'spam'],
            'deleted_status'   => ['status' => 'deleted'],
            'numeric_status'   => ['status' => 12345],
            'empty_string'     => ['status' => ''],
            'null_value'       => ['status' => null],
            'array_value'      => ['status' => ['baru']],
            'sql_injection'    => ['status' => "' OR '1'='1"],
            'missing_status'   => [],
            'oversized_string' => ['status' => str_repeat('a', 300)],
        ];

        foreach ($invalidPayloads as $scenario => $payload) {
            $response = $this->actingAs($this->admin)
                ->from("/admin/inquiries/{$inquiry->id}")
                ->patch("/admin/inquiries/{$inquiry->id}", $payload);

            $response->assertStatus(302, "Scenario [{$scenario}] did not return 302 redirect.");
            $response->assertSessionHasErrors(['status'], "Scenario [{$scenario}] failed to trigger status validation error.");
            $this->assertEquals('baru', $inquiry->fresh()->status, "Scenario [{$scenario}] modified the database status unexpectedly.");
        }
    }

    /**
     * Test unauthenticated guest cannot update inquiry status.
     */
    public function test_guest_cannot_update_inquiry_status(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'baru']);

        $response = $this->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'dihubungi',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
        $this->assertEquals('baru', $inquiry->fresh()->status);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 3: Bilingual Filtering & Global Status Tab Aggregates
    |--------------------------------------------------------------------------
    */

    /**
     * Test filtering handles bilingual values interchangeably and accurately isolates records.
     */
    public function test_inquiries_bilingual_filtering_isolation(): void
    {
        // Seed exact distribution with mixed English and Indonesian values
        Inquiry::factory()->create(['name' => 'Client New 1', 'status' => 'new']);
        Inquiry::factory()->create(['name' => 'Client New 2', 'status' => 'new']);
        Inquiry::factory()->create(['name' => 'Client Baru 3', 'status' => 'baru']);

        Inquiry::factory()->create(['name' => 'Client Contacted 1', 'status' => 'contacted']);
        Inquiry::factory()->create(['name' => 'Client Dihubungi 2', 'status' => 'dihubungi']);

        Inquiry::factory()->create(['name' => 'Client Archived 1', 'status' => 'archived']);
        Inquiry::factory()->create(['name' => 'Client Archived 2', 'status' => 'archived']);
        Inquiry::factory()->create(['name' => 'Client Diarsipkan 3', 'status' => 'diarsipkan']);

        // Total: 3 (baru/new) + 2 (dihubungi/contacted) + 3 (diarsipkan/archived) = 8

        // 1. Filter by 'baru' -> 3 records
        $resBaru = $this->actingAs($this->admin)->get('/admin/inquiries?status=baru');
        $resBaru->assertStatus(200);
        $resBaru->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Inquiries/Index')
            ->has('inquiries.data', 3)
            ->where('filters.status', 'baru')
        );

        // 2. Filter by 'new' -> same 3 records
        $resNew = $this->actingAs($this->admin)->get('/admin/inquiries?status=new');
        $resNew->assertStatus(200);
        $resNew->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 3)
            ->where('filters.status', 'new')
        );

        // 3. Filter by 'dihubungi' -> 2 records
        $resDihubungi = $this->actingAs($this->admin)->get('/admin/inquiries?status=dihubungi');
        $resDihubungi->assertStatus(200);
        $resDihubungi->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 2)
            ->where('filters.status', 'dihubungi')
        );

        // 4. Filter by 'contacted' -> same 2 records
        $resContacted = $this->actingAs($this->admin)->get('/admin/inquiries?status=contacted');
        $resContacted->assertStatus(200);
        $resContacted->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 2)
            ->where('filters.status', 'contacted')
        );

        // 5. Filter by 'diarsipkan' -> 3 records
        $resDiarsipkan = $this->actingAs($this->admin)->get('/admin/inquiries?status=diarsipkan');
        $resDiarsipkan->assertStatus(200);
        $resDiarsipkan->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 3)
            ->where('filters.status', 'diarsipkan')
        );

        // 6. Filter by 'archived' -> same 3 records
        $resArchived = $this->actingAs($this->admin)->get('/admin/inquiries?status=archived');
        $resArchived->assertStatus(200);
        $resArchived->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 3)
            ->where('filters.status', 'archived')
        );
    }

    /**
     * Test status tab counts maintain global scope regardless of active filter or search query.
     */
    public function test_inquiries_tab_counts_remain_global(): void
    {
        Inquiry::factory()->count(4)->create(['status' => 'new']);
        Inquiry::factory()->count(3)->create(['status' => 'contacted']);
        Inquiry::factory()->count(5)->create(['status' => 'archived']);

        // Query with status filter AND search term
        $response = $this->actingAs($this->admin)->get('/admin/inquiries?status=dihubungi&search=something');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->where('status_counts.all', 12)
            ->where('status_counts.baru', 4)
            ->where('status_counts.dihubungi', 3)
            ->where('status_counts.diarsipkan', 5)
        );
    }

    /**
     * Test unknown status filter and uppercase/whitespace status queries.
     */
    public function test_inquiries_filter_whitespace_case_insensitivity_and_unknown(): void
    {
        Inquiry::factory()->create(['status' => 'baru']);

        // Uppercase and whitespace trimmed: "  BARU  "
        $resTrim = $this->actingAs($this->admin)->get('/admin/inquiries?status=%20%20BARU%20%20');
        $resTrim->assertStatus(200);
        $resTrim->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 1)
        );

        // Unknown status string returns empty collection gracefully
        $resUnknown = $this->actingAs($this->admin)->get('/admin/inquiries?status=nonexistent_xyz');
        $resUnknown->assertStatus(200);
        $resUnknown->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 0)
        );

        // All / semua returns everything
        $resAll = $this->actingAs($this->admin)->get('/admin/inquiries?status=all');
        $resAll->assertStatus(200);
        $resAll->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 1)
        );

        $resSemua = $this->actingAs($this->admin)->get('/admin/inquiries?status=semua');
        $resSemua->assertStatus(200);
        $resSemua->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 1)
        );
    }

    /**
     * Test empty database returns zeroed tab counts without error.
     */
    public function test_inquiries_empty_database_zero_counts(): void
    {
        $this->assertEquals(0, Inquiry::count());

        $response = $this->actingAs($this->admin)->get('/admin/inquiries');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->where('status_counts.all', 0)
            ->where('status_counts.baru', 0)
            ->where('status_counts.dihubungi', 0)
            ->where('status_counts.diarsipkan', 0)
            ->has('inquiries.data', 0)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 4: Multi-Column Search & Operator Precedence Hardening
    |--------------------------------------------------------------------------
    */

    /**
     * Test search matches across each of the 5 targeted fields.
     */
    public function test_inquiries_search_matches_all_five_fields(): void
    {
        $inq1 = Inquiry::factory()->create([
            'name'          => 'Alexander Sterling',
            'email'         => 'unique1@example.com',
            'phone'         => '111111',
            'site_location' => 'Geneva',
            'message'       => 'Hello world',
        ]);
        $inq2 = Inquiry::factory()->create([
            'name'          => 'Bella Vance',
            'email'         => 'botanical.architect@test.org',
            'phone'         => '222222',
            'site_location' => 'Zurich',
            'message'       => 'Hello world',
        ]);
        $inq3 = Inquiry::factory()->create([
            'name'          => 'Cedric Diggory',
            'email'         => 'unique3@example.com',
            'phone'         => '+62 812 9988 7766',
            'site_location' => 'Basel',
            'message'       => 'Hello world',
        ]);
        $inq4 = Inquiry::factory()->create([
            'name'          => 'Diana Prince',
            'email'         => 'unique4@example.com',
            'phone'         => '444444',
            'site_location' => 'Lugano Waterfront Estate',
            'message'       => 'Hello world',
        ]);
        $inq5 = Inquiry::factory()->create([
            'name'          => 'Edward Norton',
            'email'         => 'unique5@example.com',
            'phone'         => '555555',
            'site_location' => 'Bern',
            'message'       => 'Special terraced amphitheater and meditation garden',
        ]);

        // Search 1: Name
        $resName = $this->actingAs($this->admin)->get('/admin/inquiries?search=Alexander');
        $resName->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 1)
            ->where('inquiries.data.0.id', $inq1->id)
        );

        // Search 2: Email
        $resEmail = $this->actingAs($this->admin)->get('/admin/inquiries?search=botanical.architect');
        $resEmail->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 1)
            ->where('inquiries.data.0.id', $inq2->id)
        );

        // Search 3: Phone
        $resPhone = $this->actingAs($this->admin)->get('/admin/inquiries?search=9988 7766');
        $resPhone->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 1)
            ->where('inquiries.data.0.id', $inq3->id)
        );

        // Search 4: Site Location
        $resLoc = $this->actingAs($this->admin)->get('/admin/inquiries?search=Lugano Waterfront');
        $resLoc->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 1)
            ->where('inquiries.data.0.id', $inq4->id)
        );

        // Search 5: Message content
        $resMsg = $this->actingAs($this->admin)->get('/admin/inquiries?search=amphitheater');
        $resMsg->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 1)
            ->where('inquiries.data.0.id', $inq5->id)
        );
    }

    /**
     * CRITICAL ADVERSARIAL TEST: Operator Precedence Isolation.
     * When combining status filter and search query, records of a different status
     * MUST NOT leak into the results even if their name/message matches the search keyword!
     */
    public function test_inquiries_search_and_status_precedence_isolation(): void
    {
        // Inquiry A is ARCHIVED, but has "Botanical" in its name
        $inquiryA = Inquiry::factory()->create([
            'name'   => 'Botanical Sanctuary (Archived)',
            'status' => 'archived',
        ]);

        // Inquiry B is BARU, and also has "Botanical" in its name
        $inquiryB = Inquiry::factory()->create([
            'name'   => 'Botanical Courtyard (Baru)',
            'status' => 'baru',
        ]);

        // Filter: status=baru AND search=Botanical
        $response = $this->actingAs($this->admin)->get('/admin/inquiries?status=baru&search=Botanical');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 1)
            ->where('inquiries.data.0.id', $inquiryB->id)
        );

        // Reverse Filter: status=diarsipkan AND search=Botanical
        $resRev = $this->actingAs($this->admin)->get('/admin/inquiries?status=diarsipkan&search=Botanical');
        $resRev->assertStatus(200);
        $resRev->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 1)
            ->where('inquiries.data.0.id', $inquiryA->id)
        );
    }

    /**
     * Test adversarial search keywords: SQL wildcards, quotes, SQL injection probes, and Unicode.
     */
    public function test_inquiries_search_resilient_to_adversarial_and_special_characters(): void
    {
        Inquiry::factory()->create([
            'name'    => '🌿 Studio Taman Tropis 🌺',
            'message' => 'Lanskap ramah lingkungan dengan batu kali & air terjun.',
        ]);

        Inquiry::factory()->create([
            'name'    => "L'Hôpital & Château d'Eau",
            'message' => 'Renovasi taman klasik 100% organik.',
        ]);

        $probes = [
            'percent_wildcard'   => '%',
            'underscore'         => '_',
            'single_quote'       => "'",
            'double_quote'       => '"',
            'sql_injection_or'   => "' OR 1=1 --",
            'sql_union_probe'    => "' UNION SELECT NULL, NULL, NULL --",
            'xss_script_probe'   => '<script>alert("xss")</script>',
            'unicode_search'     => 'Studio Taman',
            'accented_search'    => "Château d'Eau",
            'whitespace_padded'  => '   Studio Taman   ',
        ];

        foreach ($probes as $desc => $term) {
            $response = $this->actingAs($this->admin)->get('/admin/inquiries?search=' . urlencode($term));
            $response->assertStatus(200, "Search probe [{$desc}] failed with non-200 status.");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 5: Deletion Lifecycle, Boundary IDs & Authentication
    |--------------------------------------------------------------------------
    */

    /**
     * Test deleting inquiries across all possible statuses (baru, dihubungi, diarsipkan).
     */
    public function test_inquiry_can_be_deleted_from_any_status(): void
    {
        $statuses = ['new', 'baru', 'contacted', 'dihubungi', 'archived', 'diarsipkan'];

        foreach ($statuses as $st) {
            $inquiry = Inquiry::factory()->create(['status' => $st]);

            $response = $this->actingAs($this->admin)->delete("/admin/inquiries/{$inquiry->id}");

            $response->assertStatus(302);
            $response->assertRedirect('/admin/inquiries');
            $response->assertSessionHas('success', 'Pesan konsultasi berhasil dihapus.');
            $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
        }
    }

    /**
     * Test idempotency and boundary IDs on deletion.
     */
    public function test_inquiry_deletion_edge_cases_and_invalid_ids(): void
    {
        $inquiry = Inquiry::factory()->create();

        // 1. First deletion succeeds
        $res1 = $this->actingAs($this->admin)->delete("/admin/inquiries/{$inquiry->id}");
        $res1->assertStatus(302);
        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);

        // 2. Second deletion on same ID returns 404
        $res2 = $this->actingAs($this->admin)->delete("/admin/inquiries/{$inquiry->id}");
        $res2->assertStatus(404);

        // 3. Deleting non-existent ID returns 404
        $res3 = $this->actingAs($this->admin)->delete('/admin/inquiries/888888');
        $res3->assertStatus(404);

        // 4. Deleting negative or non-numeric ID returns 404
        $res4 = $this->actingAs($this->admin)->delete('/admin/inquiries/-99');
        $res4->assertStatus(404);

        $res5 = $this->actingAs($this->admin)->delete('/admin/inquiries/abc');
        $res5->assertStatus(404);
    }

    /**
     * Test unauthenticated guest cannot delete inquiries.
     */
    public function test_guest_cannot_delete_inquiry(): void
    {
        $inquiry = Inquiry::factory()->create();

        $response = $this->delete("/admin/inquiries/{$inquiry->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 6: Detail View (Show) Boundary Inputs & Nullability
    |--------------------------------------------------------------------------
    */

    /**
     * Test inquiry detail view renders with all nullable fields set to null.
     */
    public function test_inquiry_detail_view_handles_null_attributes(): void
    {
        $inquiry = Inquiry::factory()->create([
            'phone'          => null,
            'site_location'  => null,
            'estimated_area' => null,
            'budget_range'   => null,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/inquiries/{$inquiry->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Inquiries/Show')
            ->where('inquiry.id', $inquiry->id)
            ->where('inquiry.phone', null)
            ->where('inquiry.site_location', null)
            ->where('inquiry.estimated_area', null)
            ->where('inquiry.budget_range', null)
        );
    }

    /**
     * Test inquiry detail view with extremely long consultation message.
     */
    public function test_inquiry_detail_view_handles_oversized_message(): void
    {
        $hugeMessage = str_repeat("StudioKode Lanskap Tropis Lestari. ", 300); // ~10,500 chars

        $inquiry = Inquiry::factory()->create([
            'message' => $hugeMessage,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/inquiries/{$inquiry->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->where('inquiry.id', $inquiry->id)
            ->where('inquiry.message', $hugeMessage)
        );
    }

    /**
     * Test unauthenticated guest cannot access detail view.
     */
    public function test_guest_cannot_view_inquiry_detail(): void
    {
        $inquiry = Inquiry::factory()->create();

        $response = $this->get("/admin/inquiries/{$inquiry->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 7: Pagination Dynamics & Query String Preservation
    |--------------------------------------------------------------------------
    */

    /**
     * Test pagination with 35 records: 15 per page, preserving query parameters on page 2.
     */
    public function test_inquiries_pagination_and_query_string_preservation(): void
    {
        Inquiry::factory()->count(35)->create([
            'status' => 'baru',
            'name' => 'Villa Botanica Client',
        ]);

        // Page 1 with status filter and search query matching all 35 records
        $resP1 = $this->actingAs($this->admin)->get('/admin/inquiries?status=baru&search=Botanica&page=1');
        $resP1->assertStatus(200);
        $resP1->assertInertia(fn (Assert $page) => $page
            ->where('inquiries.current_page', 1)
            ->where('inquiries.per_page', 15)
            ->where('inquiries.total', 35)
            ->has('inquiries.data', 15)
            ->where('filters.status', 'baru')
            ->where('filters.search', 'Botanica')
        );

        // Page 2
        $resP2 = $this->actingAs($this->admin)->get('/admin/inquiries?status=baru&search=Botanica&page=2');
        $resP2->assertStatus(200);
        $resP2->assertInertia(fn (Assert $page) => $page
            ->where('inquiries.current_page', 2)
            ->has('inquiries.data', 15)
        );

        // Page 3
        $resP3 = $this->actingAs($this->admin)->get('/admin/inquiries?status=baru&search=Botanica&page=3');
        $resP3->assertStatus(200);
        $resP3->assertInertia(fn (Assert $page) => $page
            ->where('inquiries.current_page', 3)
            ->has('inquiries.data', 5)
        );

        // Out-of-bounds page returns empty data collection gracefully
        $resOOB = $this->actingAs($this->admin)->get('/admin/inquiries?page=999');
        $resOOB->assertStatus(200);
        $resOOB->assertInertia(fn (Assert $page) => $page
            ->has('inquiries.data', 0)
        );
    }
}
