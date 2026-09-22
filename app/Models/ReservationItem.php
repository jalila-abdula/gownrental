<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'gown_id',
        'rental_price',
        'security_deposit',
        'quantity',
    ];

    protected $casts = [
        'rental_price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function gown()
    {
        return $this->belongsTo(Gown::class);
    }
}
