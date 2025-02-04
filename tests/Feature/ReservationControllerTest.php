<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Reservation;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a  event
        $this->event = Event::factory()->create([
            'name' => 'This is a summer event',
            'description' => 'This is a description',
            'date' => Carbon::tomorrow(),
            'availability' => 10
        ]);
    }

    public function test_it_can_create_a_new_reservation()
    {
        $payload = [
            'customer_email' => 'rafael@gmail.com',
            'customer_name' => 'Rafael Panisset',
            'tickets_count' => 2
        ];

        $response = $this->postJson("/api/events/{$this->event->id}/reserve", $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'event_id',
                    'customer_email',
                    'customer_name',
                    'tickets_count'
                ]
            ]);

        $this->assertDatabaseHas('reservations', [
            'event_id' => $this->event->id,
            'customer_email' => 'rafael@gmail.com',
            'tickets_count' => 2
        ]);

        // Check if event availability was updated
        $this->assertEquals(8, $this->event->fresh()->availability);
    }

    public function test_it_prevents_reservations_for_past_events()
    {
        $pastEvent = Event::factory()->create([
            'date' => Carbon::yesterday(),
            'availability' => 10
        ]);

        $payload = [
            'customer_email' => 'rafael@gmail.com',
            'customer_name' => 'Rafael Panisset',
            'tickets_count' => 2
        ];

        $response = $this->postJson("/api/events/{$pastEvent->id}/reserve", $payload);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'PastEventReservationException',
                'message' => 'Cannot reserve or update tickets for past events.'
            ]);
    }

    public function test_it_prevents_reservations_exceeding_availability()
    {
        $payload = [
            'customer_email' => 'rafael@gmail.com',
            'customer_name' => 'Rafael Panisset',
            'tickets_count' => 11
        ];

        $response = $this->postJson("/api/events/{$this->event->id}/reserve", $payload);
        $response->assertStatus(422)
            ->assertJson([
                'error' => 'NotEnoughTicketsException',
                'message' => 'Not enough tickets available. Requested 11, but only 10 available.'
            ]);   
    }

    public function test_it_can_update_existing_reservation()
    {
        // Create initial reservation
        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'customer_email' => 'rafael@gmail.com',
            'customer_name' => 'Rafael Panisset',
            'tickets_count' => 2
        ]);

        $payload = [
            'customer_email' => 'rafael@gmail.com',
            'customer_name' => 'Rafael Panisset',
            'tickets_count' => 3
        ];

        $response = $this->putJson("/api/events/{$this->event->id}/reserve", $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer_name',
                    'tickets_count'
                ]
            ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'customer_name' => 'Rafael Panisset',
            'tickets_count' => 3
        ]);
    }

    public function test_it_can_cancel_reservation()
    {
        // Create a reservation to cancel
        $reservation = Reservation::factory()->create([
            'event_id' => $this->event->id,
            'customer_email' => 'rafael@gmail.com',
            'tickets_count' => 2
        ]);

        $initialAvailability = $this->event->availability;

        $response = $this->deleteJson("/api/events/{$this->event->id}/reserve", [
            'customer_email' => 'rafael@gmail.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Reservation cancelled']);

        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
        
        // Verify tickets were returned to availability
        $this->assertEquals(
            $initialAvailability + 2,
            $this->event->fresh()->availability
        );
    }
}