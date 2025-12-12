<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdFieldValue extends Model
{
    protected $fillable = [
        'ad_id','category_field_id','selected_option_id','value'
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class,'ad_id');
    }

    public function categoryField()
    {
        return $this->belongsTo(CategoryField::class,'category_field_id');
    }

    public function selectedOption()
    {
        return $this->belongsTo(CategoryFieldOption::class,'selected_option_id');
    }
}
