<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cinema extends Model
{
    use HasFactory;

    protected $table = 'catalog_cinemaunit';

    protected $fillable = [
        'uuid',
        'created_at',
        'updated_at',
        'cinema',
        'location',
        'rate_per_spot',
        'state',
        'user_id'
    ];

    public function owner(){
        return $this->belongsTo(SupplierOwner::class,'user_id','id');
    }

    public function order(){
        return $this->hasOne(UnitOrderCinemaItemMedia::class,'cinema_unit_id','id');
    }
}
