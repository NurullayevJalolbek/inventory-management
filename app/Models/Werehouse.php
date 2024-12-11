<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Werehouse extends Model
{
    protected  $table = 'warehouses';
    protected $fillable = ['material_id', 'remainder', 'price'];

}
