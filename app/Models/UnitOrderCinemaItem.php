<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOrderCinemaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cinema_unit_id',
        'unit_order_id',
        'description',
        'quantity'
    ];

    public function unit(){
        return $this->belongsTo(Cinema::class,'cinema_unit_id','id');
    }

    public function unit_order(){
        return $this->belongsTo(UnitOrder::class,'unit_order_id','id');
    }

    public function media(){
        return $this->hasMany(UnitOrderCinemaItemMedia::class,'unit_order_item_id','id');
    }
}
