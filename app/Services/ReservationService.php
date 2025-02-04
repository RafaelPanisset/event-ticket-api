<?php

namespace App\Services;

use App\Exceptions\NotEnoughTicketsException;
use App\Exceptions\PastEventReservationException;
use App\Exceptions\ReservationAlreadyExistsException;
use App\Exceptions\ReservationNotFoundException;
use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function createReservation(Event $event, string $customerEmail, string $customerName, int $ticketsCount): Reservation
    {
        if ($event->date->isPast()) {
            throw new PastEventReservationException();
        }
        
        $existingReservation = Reservation::getCustomerByEmailAndEventId($customerEmail, $event->id);

        if ($existingReservation) {
            throw new ReservationAlreadyExistsException("Reservation already exists for this event. Please update your existing reservation.");
        }

        return DB::transaction(function () use ($event, $customerEmail, $customerName, $ticketsCount) {
            // Lock the event record to prevent race conditions.
            $event = Event::where('id', $event->id)->lockForUpdate()->first();

            if ($ticketsCount > $event->availability) {
                throw new NotEnoughTicketsException("Not enough tickets available. Requested {$ticketsCount}, but only {$event->availability} available.");
            }

            $event->availability -= $ticketsCount;
            $event->save();

            return Reservation::create([
                'event_id'       => $event->id,
                'customer_email' => $customerEmail,
                'customer_name'  => $customerName,
                'tickets_count'  => $ticketsCount,
            ]);
        });
    }

    public function updateReservation(Event $event, int $newTicketsCount, string $customerEmail): Reservation
    {
        $reservation = Reservation::getCustomerByEmailAndEventId($customerEmail, $event->id);

        if (!$reservation) {
            throw new ReservationNotFoundException("Reservation not found.");
        }
       
        if ($event->date->isPast()) {
            throw new PastEventReservationException("Cannot update reservation for past events.");
        }

        return DB::transaction(function () use ($reservation, $event, $newTicketsCount) {
            $event = Event::where('id', $event->id)->lockForUpdate()->first();

            // Calculate the difference in ticket count.
            $additionalTickets = $newTicketsCount - $reservation->tickets_count;

            if ($additionalTickets > 0 && $additionalTickets > $event->availability) {
                throw new NotEnoughTicketsException("Not enough tickets available. Requested additional {$additionalTickets}, but only {$event->availability} available.");
            }

            $event->availability -= $additionalTickets;
            $event->save();

            $reservation->tickets_count = $newTicketsCount;
            $reservation->save();

            return $reservation;
        });
    }
    
    
    public function cancelReservation(Event $event, string $customerEmail): void
    {
        $reservation = Reservation::getCustomerByEmailAndEventId($customerEmail, $event->id);

        if (!$reservation) {
            throw new ReservationNotFoundException("Reservation not found.");
        }
       
        DB::transaction(function () use ($reservation) {
            $event = Event::where('id', $reservation->event_id)->lockForUpdate()->first();

            $event->availability += $reservation->tickets_count;
            $event->save();

            $reservation->delete();
        });
    }
}
