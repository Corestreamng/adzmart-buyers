<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOrderPrintItemMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_order_item_id',
        'media',
        'description',
        'public_id'
    ];

    public function order_item(){
        $this->belongsTo(UnitOrderPrintItem::class,'unit_order_item_id','id');
    }
}
