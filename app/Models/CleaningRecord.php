<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleaningRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'gown_id',
        'processed_by',
        'cleaning_date',
        'cleaning_type',
        'status',
        'cost',
        'notes',
    ];

    protected $casts = [
        'cleaning_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function gown()
    {
        return $this->belongsTo(Gown::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
