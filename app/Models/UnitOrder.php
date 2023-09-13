<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'total_amount',
        'status'
    ];

    public function payments(){
        return $this->hasMany(Payment::class,'unit_order_id','id');
    }

    public function tv_unit_items(){
        return $this->hasMany(UnitOrderTVItem::class, 'unit_order_id', 'id');
    }

    public function cinema_unit_items(){
        return $this->hasMany(UnitOrderCinemaItem::class, 'unit_order_id', 'id');
    }

    public function print_unit_items(){
        return $this->hasMany(UnitOrderPrintItem::class, 'unit_order_id', 'id');
    }

    public function radio_unit_items(){
        return $this->hasMany(UnitOrderRadioItem::class, 'unit_order_id', 'id');
    }

    public function billboard_unit_items(){
        return $this->hasMany(UnitOrderBillboardItem::class, 'unit_order_id', 'id');
    }
}
