<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    //
    protected $fillable = [
        'item_code',
        'item_name',
        'unit',
        'specification',
        'brand',
        'item_notes',
        'is_archived'
    ];
}
