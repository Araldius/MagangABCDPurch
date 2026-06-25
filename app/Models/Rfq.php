<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    use HasFactory;

    protected $table = 'rfqs';

    protected $fillable = [
        'purchase_request_id',
        'service_request_id',
        'rfq_number',
        'vendor_id',
        'note',
        'status',
        'is_sent_to_user',
        'sent_to_user_at',
        'opened_at',
        'closed_at',
        'vendor_token',
        'token_expires_at',
    ];

    protected $casts = [
        'is_sent_to_user' => 'boolean',
        'sent_to_user_at' => 'datetime',
        'opened_at'       => 'datetime',
        'closed_at'       => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /** Latest finalized quotation (backward compat for old controllers) */
    public function quotation()
    {
        return $this->hasOne(Quotation::class)->latestOfMany();
    }

    /** ALL quotations for this RFQ (one per vendor) — used by vendor selection */
    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function quotationPeriods()
    {
        return $this->hasMany(QuotationPeriod::class);
    }

    public function vendorQuotations()
    {
        return $this->hasMany(VendorQuotation::class);
    }

    public function quotationSummaries()
    {
        return $this->hasMany(QuotationSummary::class);
    }

    public function vendorSelections()
    {
        return $this->hasMany(VendorSelection::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }
}