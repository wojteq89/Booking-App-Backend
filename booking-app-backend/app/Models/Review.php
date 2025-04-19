<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
