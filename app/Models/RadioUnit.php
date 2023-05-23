<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadioUnit extends Model
{
    use HasFactory;

    protected $table = 'catalog_radiounit';

    protected $fillable = [
        'uuid',
        'created_at',
        'updated_at',
        'Mp_Code',
        'Vendor_Name',
        'Corporate_Name',
        'Station_Name',
        'State',
        'Media_Type',
        'Rate_Desc',
        'Time',
        'Duration',
        'Card_Rate',
        'Nego_Rate',
        'Nego_SC',
        'Card_SC',
        'Card_VD',
        'Nego_VD',
        'Add_VD',
        'SP_Disc',
        'Agency',
        'VAT',
        'Mon',
        'Tue',
        'Wed',
        'Thur',
        'Fri',
        'Sat',
        'Sun',
        'user_id',
        'total',
        'is_sold'
    ];

    public function owner(){
        return $this->belongsTo(SupplierOwner::class,'user_id','id');
    }

    public function order(){
        return $this->hasOne(UnitOrderRadioItem::class,'radio_unit_id','id');
    }
}
