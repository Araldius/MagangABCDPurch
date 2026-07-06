<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestItem extends Model
{
    use HasFactory;

    protected $table = 'service_request_items';

    protected $fillable = [
        'job_id',
        'item_id',
        'item_name',
        'quantity',
        'unit',
        'specification',
        'admin_notes',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->item_id)) {
                $lastItem = static::orderBy('id', 'desc')->first();
                $lastNumber = 0;
                if ($lastItem && preg_match('/^SVC-(\d{4})$/', $lastItem->item_id, $matches)) {
                    $lastNumber = (int) $matches[1];
                }
                $item->item_id = 'SVC-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relasi balik (belongsTo) ke Induk Job
    public function job()
    {
        return $this->belongsTo(ServiceRequestJob::class, 'job_id');
    }

    // Relasi ke Quotation Details (Penawaran Vendor)
    public function quotationDetails()
    {
        return $this->hasMany(QuotationDetail::class, 'service_request_item_id');
    }
}