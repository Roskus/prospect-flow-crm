<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Ticket;

use App\Mail\TicketStateChanged;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketSaveControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    #[Test]
    public function it_can_save_ticket(): void
    {
        $data = [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(3),
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'type' => fake()->randomElement(['question', 'incident', 'issue']),
            'status' => fake()->randomElement(['new', 'assigned', 'duplicated', 'closed']),
        ];

        $response = $this->post('/ticket/save', $data);

        $response->assertRedirect('/ticket');
        $this->assertDatabaseHas('ticket', $data);
    }

    #[Test]
    public function it_can_send_an_email_when_the_ticket_status_is_updated(): void
    {
        Mail::fake();

        $data = [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(3),
            'customer_id' => Customer::factory()->create(['seller_id' => auth()->id()])->id,
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'type' => fake()->randomElement(['question', 'incident', 'issue']),
            'status' => 'new',
        ];

        $this->post('/ticket/save', $data);

        $data['id'] = Ticket::all()->last()->id;
        $data['status'] = 'assigned';
        $response = $this->post('/ticket/save', $data);

        $response->assertRedirect('/ticket');
        $this->assertDatabaseHas('ticket', $data);

        Mail::assertSent(TicketStateChanged::class);
    }

    #[Test]
    public function it_can_send_an_email_when_the_ticket_is_closed(): void
    {
        Mail::fake();

        $customer = Customer::factory()->create(['seller_id' => auth()->id()]);
        $ticket = Ticket::factory()->create(['status' => 'new', 'customer_id' => $customer]);

        $ticket->status = 'closed';
        $ticket->save();

        Mail::assertSent(TicketStateChanged::class);
    }
}
