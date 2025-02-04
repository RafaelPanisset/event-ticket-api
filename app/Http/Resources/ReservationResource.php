<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'event_id'       => $this->event_id,
            'customer_email' => $this->customer_email,
            'customer_name'  => $this->customer_name,
            'tickets_count'  => $this->tickets_count,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
