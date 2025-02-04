<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\EventDeletionException;

class EventController extends Controller
{
   
    public function index(Request $request)
    {
        $query = Event::query();
    
        if ($request->has('future_only') && $request->boolean('future_only')) {
            $query->where('date', '>=', now());
        }
    
        if ($request->has('has_tickets') && $request->boolean('has_tickets')) {
            $query->where('availability', '>', 0);
        }
    
        $events = $query->get();
    
        return EventResource::collection($events);
    }
    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->validated());
        return response()->json(new EventResource($event), 201);
    }
      
    public function show(Event $event)
    {
        return response()->json(new EventResource($event));
    }

    
    public function update(UpdateEventRequest $request, Event $event)
    {
        $updatedEvent = DB::transaction(function () use ($request, $event) {
            $lockedEvent = Event::lockForUpdate()->find($event->id);
            $lockedEvent->update($request->validated());
            
            return $lockedEvent;
        });

        return response()->json(new EventResource($updatedEvent));
    }

    public function destroy(Event $event)
    {
        DB::transaction(function () use ($event) {
            $lockedEvent = Event::lockForUpdate()->findOrFail($event->id);
            if ($lockedEvent->reservations()->exists()) {
                throw new EventDeletionException();
            }
            $lockedEvent->delete();
        });

        return response()->json(null, 204);
    }
}