<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'store_category_id',
        'name',
        'description',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function parent_categories()
    {
        //dd($this);
        //$parent_categories = ParentCategory::where("category_id",$this->id)->get();
        $parent_categories = $this->hasMany(ParentCategory::class, "category_id");
        return $parent_categories;
    }

    public function products()
    {
        $products = $this->hasMany(ProductCategory::class, "category_id");
        return $products;
    }
}
