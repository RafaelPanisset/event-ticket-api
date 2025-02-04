<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'event_id',
        'customer_email',
        'customer_name',
        'tickets_count',
    ];
    
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public static function getCustomerByEmailAndEventId($email, $eventId)
    {
        return self::where('customer_email', $email)
            ->where('event_id', $eventId)
            ->first();
    }
}
