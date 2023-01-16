<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Api\Contact;

use App\Models\Industry;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactCreateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $jsonStructureContact = [
        'id',
    ];

    /**
     * @test
     */
    public function can_create_contact(): void
    {
        $user = $this->signIn();
        $this->actingAs($user, 'api');

        Industry::factory()->create();
        $lead = Lead::factory()->create();

        $data = [
            'lead_id' => $lead->id,
            'email' => 'email@example.com',
            'first_name' => 'Test First Name',
            'phone' => 666666666,
        ];

        $response = $this->json('POST', '/api/contact/', $data);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'contact' => $this->jsonStructureContact,
        ]);

        $contact_id = $response->json('contact.id');
        $response->assertJsonFragment([
            'contact' => [
                'id' => $contact_id,
            ],
        ]);

        $this->assertDatabaseHas('contact', [
            'id' => $contact_id,
            'lead_id' => $lead->id,
            'email' => 'email@example.com',
            'first_name' => 'Test First Name',
            'phone' => 666666666,
        ]);

        $this->get('/lead/update/'.$lead->id)
            ->assertSee($data['email'])
            ->assertSee($data['first_name'])
            ->assertSee($data['phone']);
    }

    /**
     * @test
     */
    public function create_contact_have_missing_parameters(): void
    {
        $user = $this->signIn();
        $this->actingAs($user, 'api');

        $response = $this->json('POST', '/api/contact/', [
            'email' => 'email@example.com',
        ]);

        $response->assertStatus(422);

        $response->assertJson([
            'message' => 'The lead id field is required. (and 2 more errors)',
            'errors' => [
                'lead_id' => [
                    'The lead id field is required.',
                ],
                'first_name' => [
                    'The first name field is required.',
                ],
                'phone' => [
                    'The phone field is required.',
                ],
            ],
        ]);
    }
}
