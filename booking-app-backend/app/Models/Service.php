<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'category',
        'location',
        'description',
        'opening_hours',
        'images',
        'user_id',
    ];
    
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }
}
