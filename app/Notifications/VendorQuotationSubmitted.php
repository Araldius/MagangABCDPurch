<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Rfq;
use App\Models\Vendor;

class VendorQuotationSubmitted extends Notification
{
    use Queueable;

    public $rfq;
    public $vendor;

    /**
     * Create a new notification instance.
     */
    public function __construct(Rfq $rfq, Vendor $vendor)
    {
        $this->rfq = $rfq;
        $this->vendor = $vendor;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $docNo = $this->rfq->purchaseRequest ? $this->rfq->purchaseRequest->document_number : ($this->rfq->serviceRequest ? $this->rfq->serviceRequest->document_number : 'Unknown Document');
        
        // Use property access instead of method since type might be handled in controller or by accessor
        $category = $this->rfq->serviceRequest ? 'service' : 'goods';
        $prId = $this->rfq->purchaseRequest ? $this->rfq->purchaseRequest->id : ($this->rfq->serviceRequest ? $this->rfq->serviceRequest->id : null);
        
        return [
            'vendor_name' => $this->vendor->vendor_name ?? $this->vendor->name ?? 'A Vendor',
            'rfq_number' => $this->rfq->rfq_number,
            'rfq_id' => $this->rfq->id,
            'pr_id' => $prId,
            'document_number' => $docNo,
            'category' => $category,
            'message' => 'submitted a quotation for',
        ];
    }
}