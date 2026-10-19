<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewPurchaseRequestNotification extends Notification
{
    use Queueable;

    public $request;
    public $type; 

    public function __construct($request, $type = 'goods')
    {
        $this->request = $request;
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $docNo = $this->request->document_number ?? 'PR/SR';
        $isService = $this->type === 'service';
        $plant = $this->request->plant ?? '-';
        $dept = $this->request->department ?? '-';
        $creatorName = $this->request->user ? $this->request->user->name : 'User';
        $needDate = $this->request->need_date ?? $this->request->requested_date ?? '-';

        $itemCount = 0;
        if ($isService && $this->request->jobs) {
            foreach ($this->request->jobs as $job) {
                $itemCount += $job->items ? $job->items->count() : 0;
            }
        } elseif ($this->request->items) {
            $itemCount = $this->request->items->count();
        }

        $mail = (new MailMessage)
            ->subject('[New Request] Pengajuan Baru - ' . $docNo)
            ->greeting('Halo Tim Purchasing,')
            ->line('User **' . $creatorName . '** telah membuat pengajuan baru **' . $docNo . '** (' . ($isService ? 'Service Request' : 'Purchase Request') . ').')
            ->line('**Plant / Departemen:** ' . $plant . ' / ' . $dept)
            ->line('**Tanggal Dibutuhkan:** ' . $needDate)
            ->line('**Jumlah Item:** ' . $itemCount . ' item');

        $url = url('/purchasing-approval?open_pr=' . $this->request->id . '&category=' . $this->type);
        $mail->action('Tinjau Pengajuan (Purchasing Approval)', $url)
            ->line('Silakan periksa dan berikan persetujuan untuk memulai proses pencarian vendor.')
            ->line('Terima kasih.');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $creatorName = $this->request->user ? $this->request->user->name : 'User';

        return [
            'vendor_name' => $creatorName,
            'rfq_id' => null, 
            'pr_id' => $this->request->id,
            'document_number' => $this->request->document_number,
            'category' => $this->type,
            'message' => 'telah membuat pengajuan baru untuk',
        ];
    }
}
