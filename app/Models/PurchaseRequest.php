<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\History;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'document_number', 
        'title', 
        'department', 
        'priority', 
        'plant', 
        'submission_date', 
        'requested_date', 
        'need_date', 
        'note', 
        'status',
        'attachment_path',
        'purchasing_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }

    public function rfqs()
    {
        return $this->hasMany(Rfq::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }

    public function getDisplayDocAttribute()
    {
        return $this->document_number;
    }

    public function getDisplayTitleAttribute()
    {
        return $this->title;
    }

    public function getItemCountAttribute()
    {
        // Menghitung jumlah item barang
        return $this->items ? $this->items->count() : 0;
    }
}
