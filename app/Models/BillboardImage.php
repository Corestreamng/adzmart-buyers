<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillboardImage extends Model
{
    use HasFactory;

    protected $table = 'catalog_billboardimage';

    protected $fillable = [
        'image',
        'created_at',
        'updated_at',
        'image_public_id',
        'reference_id'
    ];

    public function billboard(){
        return $this->belongsTo(Billboard::class, 'reference_id','reference_id');
    }
}
