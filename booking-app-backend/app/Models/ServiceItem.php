<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'description',
        'price',
        'duration',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
