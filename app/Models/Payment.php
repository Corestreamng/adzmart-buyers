<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_order_id',
        'amount',
        'payment_method',
        'payment_date',
        'payment_metadata'
    ];

    public function unit_order(){
        return $this->belongsTo(UnitOrder::class,'unit_order_id','id');
    }
}
