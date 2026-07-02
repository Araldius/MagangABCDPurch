<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class VendorSelectedNotification extends Notification
{
    public $request;
    public $type; 

    public function __construct($request, $type)
    {
        $this->request = $request;
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'vendor_name' => 'Tim Purchasing',
            'rfq_id' => null, 
            'pr_id' => $this->request->id,
            'document_number' => $this->request->document_number,
            'category' => $this->type,
            'message' => 'telah menyelesaikan pemilihan vendor untuk',
        ];
    }
}