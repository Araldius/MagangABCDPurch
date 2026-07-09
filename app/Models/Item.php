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

    public function getFullNameAttribute()
    {
        $parts = [$this->item_name];
        if (!empty($this->specification)) {
            $parts[] = $this->specification;
        }
        if (!empty($this->brand)) {
            $parts[] = $this->brand;
        }
        return implode('_', $parts);
    }
}
