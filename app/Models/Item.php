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
        'is_archived',
        'type'
    ];

    public function getFullNameAttribute()
    {
        $spec = empty($this->specification) ? 'nsp' : $this->specification;
        $brand = empty($this->brand) ? 'nbr' : $this->brand;
        $fullName = $this->item_name . '_' . $spec . '_' . $brand;
        return \Illuminate\Support\Str::limit($fullName, 40, '');
    }
}
