<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryFieldOption extends Model
{
    protected $fillable = [
        'category_field_id','parent_option_id','slug','value','label','label_l1'
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_option_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_option_id');
    }

    public function categoryField()
    {
        return $this->belongsTo(CategoryField::class,'category_field_id');
    }

    public function adFieldValues()
    {
        return $this->hasMany(AdFieldValue::class, 'selected_option_id');
    }
}
