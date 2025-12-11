<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = [
        'user_id','category_id','title','description','price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'ad_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'ad_id');
    }

    public function adFieldValues()
    {
        return $this->hasMany(AdFieldValue::class, 'ad_id');
    }
}
