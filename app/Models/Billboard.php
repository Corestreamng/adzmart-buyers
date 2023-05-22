<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billboard extends Model
{
    use HasFactory;

    protected $table = "catalog_unit";

    protected $fillable = [
        'uuid',
        'name',
        'supplier',
        'display_name',
        'adzmart_hash',
        'reference_id',
        'billboard_id',
        'latitude',
        'longitude',
        'district',
        'state',
        'postal_code',
        'country',
        'facing',
        'description',
        'unit_info',
        'unit_type_id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function images(){
        return $this->hasMany(BillboardImage::class,'reference_id','reference_id');
    }

    public function owner(){
        return $this->belongsTo(SupplierOwner::class,'user_id','id');
    }

    public function order(){
        return $this->hasOne(UnitOrderBillboardItem::class,'billboard_unit_id','id');
    }
}
