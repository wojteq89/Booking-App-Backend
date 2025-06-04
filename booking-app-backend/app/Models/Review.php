<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'service_id',
        'user_id',
        'client_name',
        'content',
        'rating',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
