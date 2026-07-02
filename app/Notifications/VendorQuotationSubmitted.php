<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\Rfq;
use App\Models\Vendor;

class VendorQuotationSubmitted extends Notification
{
    public $rfq;
    public $vendor;

    public function __construct(Rfq $rfq, Vendor $vendor)
    {
        $this->rfq = $rfq;
        $this->vendor = $vendor;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Akan langsung masuk ke tabel notifications dan memunculkan Red Dot!
    }

    public function toArray(object $notifiable): array
    {
        $docNo = $this->rfq->purchaseRequest ? $this->rfq->purchaseRequest->document_number : ($this->rfq->serviceRequest ? $this->rfq->serviceRequest->document_number : 'Unknown Document');
        $category = $this->rfq->serviceRequest ? 'service' : 'goods';
        $prId = $this->rfq->purchaseRequest ? $this->rfq->purchaseRequest->id : ($this->rfq->serviceRequest ? $this->rfq->serviceRequest->id : null);
        
        return [
            'vendor_name' => $this->vendor->vendor_name ?? $this->vendor->name ?? 'Vendor',
            'rfq_number' => $this->rfq->rfq_number,
            'rfq_id' => $this->rfq->id,
            'pr_id' => $prId,
            'document_number' => $docNo,
            'category' => $category,
            'message' => 'telah submit penawaran (quotation) untuk',
        ];
    }
}