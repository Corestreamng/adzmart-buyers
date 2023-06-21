<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertReq extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'message',
        'answerd',
        'status',
    ];

    public function owner(){
        return $this->belongsTo(User::class,'email','email');
    }
}
