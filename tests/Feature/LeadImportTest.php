<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LeadImportTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('email', 'admin@mantraheal.com')->first();
    }

    public function test_lead_import_page_loads(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('leads.import'));
        $response->assertStatus(200);
        $response->assertSee('Bulk Lead Importer');
        $response->assertSee('Download Sample CSV Template');
        $response->assertSee('Start Lead Import');
    }

    public function test_download_template_streams_valid_csv(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('leads.import.template'));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertStringContainsString('Name,Mobile,Email', $response->streamedContent());
    }

    public function test_import_csv_creates_leads(): void
    {
        $uniquePhone1 = '98765' . rand(10000, 99999);
        $uniquePhone2 = '98766' . rand(10000, 99999);

        $csvContent = "Name,Mobile,Email,City,State,Pincode,Source,Stage,Estimated Value,Notes\n";
        $csvContent .= "Test Buyer Alpha,+91 {$uniquePhone1},alpha@test.com,Gurugram,Haryana,122001,Bulk Import,New,1999,First test lead\n";
        $csvContent .= "Test Buyer Beta,+91 {$uniquePhone2},beta@test.com,Noida,Uttar Pradesh,201301,Bulk Import,Interested,2499,Second test lead\n";

        $file = UploadedFile::fake()->createWithContent('leads_test.csv', $csvContent);

        $response = $this->actingAs($this->adminUser)->post(route('leads.import.process'), [
            'csv_file' => $file,
            'duplicate_action' => 'skip',
            'assignment_mode' => 'round_robin',
            'default_stage' => 'New',
            'default_source' => 'Bulk Import',
        ]);

        $response->assertRedirect(route('leads.index'));
        $response->assertSessionHas('success');

        $lead1 = Lead::where('email', 'alpha@test.com')->first();
        $this->assertNotNull($lead1);
        $this->assertEquals('Test Buyer Alpha', $lead1->name);
        $this->assertEquals('Gurugram', $lead1->city);
        $this->assertNotNull($lead1->assigned_user_id);

        $lead2 = Lead::where('email', 'beta@test.com')->first();
        $this->assertNotNull($lead2);
        $this->assertEquals('Test Buyer Beta', $lead2->name);
        $this->assertEquals('Interested', $lead2->stage);
    }

    public function test_import_csv_skips_duplicates(): void
    {
        $existing = Lead::first();
        $this->assertNotNull($existing);

        $csvContent = "Name,Mobile,Email,City\n";
        $csvContent .= "Duplicate Lead,{$existing->mobile},duplicate@test.com,Mumbai\n";

        $file = UploadedFile::fake()->createWithContent('duplicate_test.csv', $csvContent);

        $initialCount = Lead::count();

        $response = $this->actingAs($this->adminUser)->post(route('leads.import.process'), [
            'csv_file' => $file,
            'duplicate_action' => 'skip',
            'assignment_mode' => 'unassigned',
            'default_stage' => 'New',
            'default_source' => 'Bulk Import',
        ]);

        $response->assertRedirect(route('leads.index'));
        $this->assertEquals($initialCount, Lead::count());
    }
}
