<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $table = 'service_requests';

    protected $fillable = [
        'user_id',
        'department',
        'document_number',
        'service_name',
        'submission_date',
        'requested_date',
        'business_area',
        'status',
    ];

    // Relasi ke User pembuat request
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi 1-to-Many ke tabel Jobs (Scope of Work)
    public function jobs()
    {
        return $this->hasMany(ServiceRequestJob::class, 'service_request_id');
    }

    // Relasi ke RFQ (jika nantinya diproses oleh Purchasing)
    public function rfqs()
    {
        return $this->hasMany(Rfq::class, 'service_request_id');
    }

    public function getDisplayDocAttribute()
    {
        return $this->document_number
            ?? 'SR-' . ($this->created_at ?? now())->format('Y') . '-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getDisplayTitleAttribute()
    {
        // Service Request menggunakan kolom service_name sebagai judul
        return $this->service_name;
    }

    public function getItemCountAttribute()
    {
        // Menghitung total item di dalam semua jobdesc
        $count = 0;
        if ($this->jobs) {
            foreach ($this->jobs as $job) {
                $count += $job->items ? $job->items->count() : 0;
            }
        }
        return $count;
    }
}