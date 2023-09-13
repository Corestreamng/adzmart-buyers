<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $table = 'authentication_supplier';

    protected $fillable = [
        'uuid',
        'company_name',
        'company_location',
        'rc_number',
        'government_id',
        'is_verified',
        'owner_id',
        'created_at',
        'updated_at',

    ];

    protected $hidden = [
        'password',
    ];

    public function owner(){
        return $this->belongsTo(SupplierOwner::class,'owner_id','id');
    }
}
