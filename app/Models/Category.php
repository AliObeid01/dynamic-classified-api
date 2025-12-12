<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'external_id','parent_id','name','slug','name_l1','level'
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function fields()
    {
        return $this->belongsToMany(Field::class, 'category_fields', 'category_id', 'field_id')->withTimestamps();
    }

    public function categoryFields()
    {
        return $this->hasMany(CategoryField::class);
    }

    public function ads()
    {
        return $this->hasMany(Ad::class, 'category_id');
    } 
}
