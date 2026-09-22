<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'gown_id',
        'processed_by',
        'maintenance_type',
        'description',
        'cost',
        'maintenance_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'maintenance_date' => 'date',
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
