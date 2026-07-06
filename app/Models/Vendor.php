<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\VendorQuotation;
use App\Models\VendorSelection;
use App\Models\History;

class Vendor extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'vendor_name',
        'location',
        'email',
        'status',
    ];

    public function rfqs()
    {
        return $this->hasMany(Rfq::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function vendorQuotations()
    {
        return $this->hasMany(VendorQuotation::class);
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
