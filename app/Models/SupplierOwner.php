<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOwner extends Model
{
    use HasFactory;

    protected $table = "authentication_user";

    protected $fillable = [
        'is_superuser',
        'uuid',
        'email',
        'first_name',
        'last_name',
        'phone_no',
        'phone_no',
        'is_staff',
        'supplier_id',
        'is_verified',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
    ];

    public function suplier_profile(){
        return $this->hasOne(Supplier::class,'owner_id','id');
    }
}
