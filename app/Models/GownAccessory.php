<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GownAccessory extends Model
{
    protected $table = 'gown_accessories';

    public $timestamps = false;

    protected $fillable = [
        'gown_id',
        'accessory_id',
        'quantity',
    ];

    protected $casts = [
        'gown_id' => 'integer',
        'accessory_id' => 'integer',
        'quantity' => 'integer',
    ];

    public function gown()
    {
        return $this->belongsTo(Gown::class);
    }

    public function accessory()
    {
        return $this->belongsTo(Accessory::class);
    }
}
