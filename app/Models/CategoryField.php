<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryField extends Model
{
    protected $fillable = [
        'category_id','field_id','is_mandatory'
    ];

    protected $casts = [
        'is_mandatory' => 'bool',
    ];

    // public function adFieldValues()
    // {
    //     return $this->hasMany(AdFieldValue::class, 'category_field_id');
    // }

}
