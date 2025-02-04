<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_events()
    {
        $events = Event::factory()->count(3)->create();

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'description',
                        'date',
                        'availability',
                    ]
                ]
            ]);
    }

    public function test_too_many_requests_error()
    {
        $maxAttempts = 20; 
        
        for ($i = 0; $i < $maxAttempts + 1; $i++) {
            $response = $this->getJson('/api/events');
        }

        $response->assertStatus(429)
            ->assertJson([
                'error' => 'Too Many Requests',
                'message' => 'Too Many Attempts.'
            ]);
    }

    public function test_can_create_new_event()
    {
        $eventData = [
            'name' => 'Rock in Rio',
            'description' => 'A big music festival',
            'date' => '2025-12-31',
            'availability' => 100
        ];

        $response = $this->postJson('/api/events', $eventData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'name',
                'description',
                'date',
                'availability',
            ])
            ->assertJson([
                'name' => $eventData['name'],
                'description' => $eventData['description'],
                'availability' => $eventData['availability']
            ]);

        $this->assertDatabaseHas('events', [
            'name' => $eventData['name'],
            'description' => $eventData['description']
        ]);
    }

    public function test_can_show_event()
    {
        $event = Event::factory()->create();

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'name',
                'description',
                'date',
                'availability',
            ])
            ->assertJson([
                'name' => $event->name,
                'description' => $event->description
            ]);
    }

    public function test_can_update_event()
    {
        $event = Event::factory()->create();
        $updateData = [
            'name' => 'Lollapalooza',
            'description' => 'A music festival',
            'date' => '2026-12-31',
            'availability' => 8000
        ];

        $response = $this->putJson("/api/events/{$event->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'name',
                'description',
                'date',
                'availability',
            ])
            ->assertJson([
                'name' => $updateData['name'],
                'description' => $updateData['description'],
                'availability' => $updateData['availability']
            ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => $updateData['name']
        ]);
    }

    public function test_can_delete_event_without_reservations()
    {
        $event = Event::factory()->create();

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_cannot_delete_event_with_reservations()
    {
        $event = Event::factory()->create();
        Reservation::factory()->create(['event_id' => $event->id]);

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot delete event with existing reservations'
            ]);
        
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_creating_event_with_invalid_data_returns_validation_error()
    {
        $invalidData = [
            'name' => '', 
            'date' => 'not-a-date-here', 
        ];

        $response = $this->postJson('/api/events', $invalidData);

        $response->assertStatus(422)
        ->assertJsonStructure([
            'error',
            'messages' => ['name', 'date'],
        ]);    
    }
}