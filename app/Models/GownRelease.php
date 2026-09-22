<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GownRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'processed_by',
        'release_date',
        'release_time',
        'condition_before',
        'notes',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
