<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'service_item_id',
        'service_item_name',
        'service_item_color',
        'start',
        'end',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }
}