<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'gown_return_id',
        'penalty_type',
        'amount',
        'status',
        'waived_by',
        'waived_at',
        'waiver_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'waived_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function gownReturn()
    {
        return $this->belongsTo(GownReturn::class);
    }

    public function waivedBy()
    {
        return $this->belongsTo(User::class, 'waived_by');
    }
}
