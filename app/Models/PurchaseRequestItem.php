<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'item_name',
        'quantity',
        'unit',
        'specification',
        'brand',
        'item_notes',
        'admin_notes'
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

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
}