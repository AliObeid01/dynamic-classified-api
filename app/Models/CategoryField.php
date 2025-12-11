<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryField extends Model
{
    protected $fillable = [
        'category_id','name','attribute','value_type','filter_type','is_mandatory'
    ];

    protected $casts = [
        'is_mandatory' => 'bool',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

    public function fieldOptions()
    {
        return $this->hasMany(CategoryFieldOption::class, 'category_field_id');
    }
    public function adFieldValues()
    {
        return $this->hasMany(AdFieldValue::class, 'category_field_id');
    }

}
