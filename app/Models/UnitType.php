<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    use HasFactory;

    protected $table = 'catalog_unittype';

    protected $fillable = [
        'uuid',
        'created_at',
        'updated_at',
        'name',
        'description'
    ];
}
