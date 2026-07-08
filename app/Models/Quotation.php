<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QuotationDetail;
use App\Models\VendorSelection;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'total_price',
        'note',
        'status',
        'vendor_token',
        'attachment_path',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function details()
    {
        return $this->hasMany(QuotationDetail::class, 'quotation_id');
    }

    public function vendorSelections()
    {
        return $this->hasMany(VendorSelection::class);
    }
}
