<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tier 1: Inquiry submission with full payload redirects and persists in database.
     */
    public function test_inquiry_stores_successfully_with_complete_data(): void
    {
        $payload = [
            'name' => 'Julian Finch',
            'email' => 'julian@example.com',
            'phone' => '+41 22 555 0192',
            'site_location' => 'Geneva, Switzerland',
            'estimated_area' => '500-1000 m²',
            'budget_range' => '$100k - $250k',
            'message' => 'We are seeking an organic landscape masterplan for our terraced lakeside residence.',
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Julian Finch',
            'email' => 'julian@example.com',
            'phone' => '+41 22 555 0192',
            'site_location' => 'Geneva, Switzerland',
            'estimated_area' => '500-1000 m²',
            'budget_range' => '$100k - $250k',
            'status' => 'new',
        ]);
    }

    /**
     * Tier 1: Inquiry submission with minimal required payload (name, email, message).
     */
    public function test_inquiry_stores_successfully_with_minimal_required_data(): void
    {
        $payload = [
            'name' => 'Elena Rostova',
            'email' => 'elena@example.com',
            'message' => 'Looking for courtyard garden consulting.',
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Elena Rostova',
            'email' => 'elena@example.com',
            'phone' => null,
            'site_location' => null,
            'status' => 'new',
        ]);
    }

    /**
     * Tier 1: Inquiry submission fails when name is missing.
     */
    public function test_inquiry_fails_when_name_missing(): void
    {
        $payload = [
            'email' => 'valid@example.com',
            'message' => 'Testing missing name.',
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('inquiries', 0);
    }

    /**
     * Tier 1: Inquiry submission fails when email is missing.
     */
    public function test_inquiry_fails_when_email_missing(): void
    {
        $payload = [
            'name' => 'Valid Name',
            'message' => 'Testing missing email.',
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('inquiries', 0);
    }

    /**
     * Tier 1: Inquiry submission fails when email is invalid format.
     */
    public function test_inquiry_fails_when_email_invalid_format(): void
    {
        $payload = [
            'name' => 'Valid Name',
            'email' => 'not-a-valid-email-address',
            'message' => 'Testing invalid email format.',
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('inquiries', 0);
    }

    /**
     * Tier 1: Inquiry submission fails when message is missing.
     */
    public function test_inquiry_fails_when_message_missing(): void
    {
        $payload = [
            'name' => 'Valid Name',
            'email' => 'valid@example.com',
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertSessionHasErrors(['message']);
        $this->assertDatabaseCount('inquiries', 0);
    }

    /**
     * Tier 2 Boundary: Name exceeding 255 characters is rejected.
     */
    public function test_inquiry_rejects_name_exceeding_255_characters(): void
    {
        $payload = [
            'name' => str_repeat('A', 256),
            'email' => 'valid@example.com',
            'message' => 'Testing 256 char name.',
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('inquiries', 0);
    }

    /**
     * Tier 2 Boundary: Name at exact boundary of 255 characters is accepted.
     */
    public function test_inquiry_accepts_name_at_exact_255_characters(): void
    {
        $payload = [
            'name' => str_repeat('B', 255),
            'email' => 'boundary@example.com',
            'message' => 'Testing 255 char name boundary.',
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('inquiries', 1);
    }

    /**
     * Tier 2 Boundary: Message exceeding 5000 characters is rejected.
     */
    public function test_inquiry_rejects_message_exceeding_5000_characters(): void
    {
        $payload = [
            'name' => 'Valid Name',
            'email' => 'valid@example.com',
            'message' => str_repeat('M', 5001),
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertSessionHasErrors(['message']);
        $this->assertDatabaseCount('inquiries', 0);
    }

    /**
     * Tier 2 Boundary / Security: XSS script tags in message are safely persisted without execution.
     */
    public function test_inquiry_persists_raw_xss_payload_safely(): void
    {
        $xss = "<script>alert('xss_attack');</script>";
        $payload = [
            'name' => 'Security Tester',
            'email' => 'security@example.com',
            'message' => $xss,
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', [
            'name' => 'Security Tester',
            'message' => $xss,
        ]);
    }

    /**
     * Tier 2 Boundary: Multilingual Unicode characters in form inputs are persisted cleanly.
     */
    public function test_inquiry_persists_multilingual_unicode_content(): void
    {
        $payload = [
            'name' => '田中 太郎 (Taro Tanaka)',
            'email' => 'tanaka@example.jp',
            'site_location' => '京都市左京区 (Kyoto Sakyo-ku)',
            'message' => '苔庭の設計と石組みをお願いしたいです。Ancient moss garden restoration required.',
        ];

        $response = $this->post('/inquiries', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', [
            'email' => 'tanaka@example.jp',
            'site_location' => '京都市左京区 (Kyoto Sakyo-ku)',
        ]);
    }
}
