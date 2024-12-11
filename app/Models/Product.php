<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected  $fillable = ['name', 'code'];


    protected $table = 'products';

    public function materials(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'product_materials')
            ->withPivot('quantity');
    }
}
