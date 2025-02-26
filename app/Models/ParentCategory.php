<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentCategory extends Model
{
    use HasFactory;

    protected $table = 'rel_parent_category';

    protected $fillable = [
        'category_id',
        'parent_category_id',
    ];

}
