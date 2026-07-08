<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorQuotationEditLink extends Notification
{
    use Queueable;

    public $rfq;
    public $vendor;
    public $token;

    public function __construct($rfq, $vendor, $token)
    {
        $this->rfq = $rfq;
        $this->vendor = $vendor;
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $prDocNumber = $this->rfq->purchaseRequest->document_number ?? 
                       ($this->rfq->serviceRequest->document_number ?? 'N/A');
                       
        return (new MailMessage)
                    ->subject('Quotation Token & Edit Link - ' . $prDocNumber)
                    ->greeting('Hello ' . $this->vendor->vendor_name . ',')
                    ->line('Thank you for submitting your quotation for ' . $prDocNumber . '.')
                    ->line('If you need to make changes or continue editing your quotation later, please use your unique token:')
                    ->line('**Token: ' . $this->token . '**')
                    ->action('Edit Quotation', url('/vendor-portal/edit/' . $this->token))
                    ->line('Thank you for your cooperation!');
    }
}