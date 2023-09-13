<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintUnit extends Model
{
    use HasFactory;

    protected $table = 'catalog_printunit';

    protected $fillable = [
        'uuid',
        'coverage',
        'publisher',
        'title',
        'type',
        'size',
        'position',
        'rate',
        'agency_discount',
        'amount',
        'vat',
        'total',
        'user_id',
        'created_at',
        'updated_at',
        'is_sold'
    ];

    public function owner(){
        return $this->belongsTo(SupplierOwner::class,'user_id','id');
    }

    public function order(){
        return $this->hasOne(UnitOrderPrintItem::class,'print_unit_id','id');
    }
}
