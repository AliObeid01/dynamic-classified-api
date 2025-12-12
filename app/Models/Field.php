<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $fillable = [
        'name','attribute','value_type','filter_type'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_fields', 'field_id', 'catgeory_id')->withTimestamps();
    }

    public function categoryFields()
    {
        return $this->hasMany(CategoryField::class);
    }

    public function options()
    {
        return $this->hasMany(CategoryFieldOption::class);
    }

}
