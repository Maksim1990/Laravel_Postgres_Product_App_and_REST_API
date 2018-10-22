<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];
    protected $with = ['products'];

    public function products(){
        return $this->hasManyThrough(
            Product::class,
            ProductCategoryPivot::class,
            'category_id',
            'id',
            'id',
            'product_id'
        );
    }
}
