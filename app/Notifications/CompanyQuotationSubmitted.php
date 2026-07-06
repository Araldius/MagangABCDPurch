<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Models\Quotation;

class CompanyQuotationSubmitted extends Notification
{
    use Queueable;

    public $rfq;
    public $vendor;
    public $quotation;

    public function __construct(Rfq $rfq, Vendor $vendor, Quotation $quotation)
    {
        $this->rfq = $rfq;
        $this->vendor = $vendor;
        $this->quotation = $quotation;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $pr = $this->rfq->purchaseRequest ?? $this->rfq->serviceRequest;
        $docNum = $pr ? $pr->document_number : 'RFQ';
        $isService = (bool) $this->rfq->service_request_id;

        $mailMessage = (new MailMessage)
            ->subject('New Quotation Submitted - ' . $this->vendor->vendor_name)
            ->greeting('Dear Team,')
            ->line('Vendor **' . $this->vendor->vendor_name . '** has submitted a quotation for ' . ($isService ? 'Service Request' : 'Purchase Request') . ' **' . $docNum . '**.')
            ->line('**Quotation Details:**')
            ->line('Total Quote: **Rp ' . number_format($this->quotation->total_price, 0, ',', '.') . '**');

        // Add itemized list
        $mailMessage->line('---');
        foreach ($this->quotation->details as $idx => $detail) {
            $itemName = '-';
            if ($isService && $detail->serviceRequestItem) {
                $itemName = $detail->serviceRequestItem->item_name;
            } elseif ($detail->purchaseRequestItem) {
                $itemName = $detail->purchaseRequestItem->item_name;
            }
            $mailMessage->line(($idx + 1) . '. **' . $itemName . '** | Qty: ' . $detail->offered_quantity . ' ' . $detail->offered_unit . ' | Price: Rp ' . number_format($detail->offered_price_per_item, 0, ',', '.') . ' | Subtotal: Rp ' . number_format($detail->offered_quantity * $detail->offered_price_per_item, 0, ',', '.'));
        }
        $mailMessage->line('---');

        // Attach quotation document if uploaded
        if ($this->quotation->attachment_path) {
            $filePath = storage_path('app/public/' . $this->quotation->attachment_path);
            if (file_exists($filePath)) {
                $mailMessage->attach($filePath);
            }
        }

        if ($this->quotation->vendor_token) {
            $editUrl = url('/vendor-portal/edit/' . $this->quotation->vendor_token);
            $mailMessage->action('Edit / Replace Quotation', $editUrl)
                ->line('To update this quotation or upload a new attachment, use the secure link above. Do not share this link with unauthorized parties.');
        }

        return $mailMessage;
    }
}
