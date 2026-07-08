<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelectionItem extends Model
{
    protected $fillable = [
        'vendor_selection_id',
        'quotation_summary_id',
        'purchase_request_item_id',  
        'service_request_item_id',
        'final_price_per_item',
        'final_quantity',
        'final_unit',
        'final_specification',
        'final_brand',
        'notes',
    ];

    public function vendorSelection()
    {
        return $this->belongsTo(VendorSelection::class);
    }

    public function quotationSummary()
    {
        return $this->belongsTo(QuotationSummary::class);
    }

    public function purchaseRequestItem()
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }

    public function serviceRequestItem()
    {
        return $this->belongsTo(ServiceRequestItem::class);
    }
}