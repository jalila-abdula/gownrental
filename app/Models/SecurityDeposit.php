<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'amount',
        'deducted_amount',
        'refund_amount',
        'status',
        'refunded_at',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deducted_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
