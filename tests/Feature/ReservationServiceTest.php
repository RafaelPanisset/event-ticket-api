<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\ReservationService;
use App\Models\Event;
use App\Models\Reservation;
use Carbon\Carbon;
use DB;
use App\Exceptions\InsufficientTicketsException;

class ReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReservationService $reservationService;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reservationService = app(ReservationService::class); 
        $this->event = Event::factory()->create([
            'date' => Carbon::tomorrow(),
            'availability' => 10,
        ]);
    }

    public function test_it_creates_new_reservation_with_valid_data()
    {
        $reservation = $this->reservationService->createReservation(
            $this->event,
            'rafael@gmail.com',
            'Rafael Panisset',
            2
        );

        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertEquals('rafael@gmail.com', $reservation->customer_email);
        $this->assertEquals(2, $reservation->tickets_count);
        $this->assertEquals(8, $this->event->fresh()->availability);
    }

    public function test_it_updates_existing_reservation()
    {
        $initialReservation = $this->reservationService->createReservation(
            $this->event,
            'rafael@gmail.com',
            'Rafael Panisset',
            2
        );

        $updatedReservation = $this->reservationService->updateReservation(
            $this->event,
            3,
            'rafael@gmail.com'
        );
        

        $this->assertEquals($initialReservation->id, $updatedReservation->id);
        $this->assertEquals(3, $updatedReservation->tickets_count);
        $this->assertEquals(7, $this->event->fresh()->availability);
    }

    public function test_it_updates_reservation_to_reduce_ticket_count_and_refunds_availability()
    {
        $reservation = $this->reservationService->createReservation(
            $this->event,
            'reduce@example.com',
            'Rafael Panisset',
            4
        );

        $this->assertEquals(6, $this->event->fresh()->availability);

        $updatedReservation = $this->reservationService->updateReservation(
            $this->event,
            2,
            'reduce@example.com'
        );

        $this->assertEquals(2, $updatedReservation->tickets_count);

        $this->assertEquals(8, $this->event->fresh()->availability);
    }


    public function test_it_handles_concurrent_reservations()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Not enough tickets available.");

        DB::transaction(function () {
            $reservation1 = $this->reservationService->createReservation(
                $this->event,
                'test1@example.com',
                'Rafael Panisset',
                6
            );

            $reservation2 = $this->reservationService->createReservation(
                $this->event,
                'test2@example.com',
                'Jane Doe',
                6
            );
        });
    }

    public function test_it_properly_cancels_reservation()
    {
        $reservation = $this->reservationService->createReservation(
            $this->event,
            'rafael@gmail.com',
            'Rafael Panisset',
            2
        );

        $initialAvailability = $this->event->fresh()->availability; 

        $this->reservationService->cancelReservation($this->event, 'rafael@gmail.com');

        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
        $this->assertEquals(
            $initialAvailability + 2,
            $this->event->fresh()->availability
        );
    }
}