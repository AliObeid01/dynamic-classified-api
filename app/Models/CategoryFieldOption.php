<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryFieldOption extends Model
{
    protected $fillable = [
        'field_id','parent_id','value','label'
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function field()
    {
        return $this->belongsTo(Field::class,'field_id');
    }

    public function adFieldValues()
    {
        return $this->hasMany(AdFieldValue::class, 'selected_option_id');
    }
}
