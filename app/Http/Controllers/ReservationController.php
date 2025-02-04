<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Requests\CancelReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Event;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Exception;
use App\Exceptions\InsufficientTicketsException;

class ReservationController extends Controller
{
    protected $reservationService;
    
    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }
    
   
    public function reserve(StoreReservationRequest $request, Event $event)
    {
        $reservation = $this->reservationService->createReservation(
            $event,
            $request->input('customer_email'),
            $request->input('customer_name'),
            $request->input('tickets_count')
        );
        return new ReservationResource($reservation);
    }


    public function update(UpdateReservationRequest $request, Event $event)
    {
        $updatedReservation = $this->reservationService->updateReservation(
            $event,
            $request->input('tickets_count'),
            $request->input('customer_email')
        );
        return new ReservationResource($updatedReservation);
    }

    public function cancel(CancelReservationRequest $request, Event $event)
    {
        $this->reservationService->cancelReservation($event, $request->input('customer_email'));
        return response()->json(['message' => 'Reservation cancelled']);
    }
}
