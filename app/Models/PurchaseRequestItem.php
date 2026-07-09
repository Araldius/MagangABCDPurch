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
        $spec = empty($this->specification) ? 'nsp' : $this->specification;
        $brand = empty($this->brand) ? 'nbr' : $this->brand;
        $fullName = $this->item_name . '_' . $spec . '_' . $brand;
        return \Illuminate\Support\Str::limit($fullName, 40, '');
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
}