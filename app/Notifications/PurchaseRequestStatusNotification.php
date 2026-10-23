<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PurchaseRequestStatusNotification extends Notification
{
    use Queueable;

    public $request;
    public $statusType; // 'approved', 'rejected', 'reopened'
    public $notes;
    public $type; // 'goods' or 'service'

    public function __construct($request, $statusType = 'approved', $notes = null, $type = 'goods')
    {
        $this->request = $request;
        $this->statusType = $statusType;
        $this->notes = $notes;
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
        $statusLabels = [
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'reopened' => 'Dibuka Kembali (Reopened)',
        ];
        $label = $statusLabels[$this->statusType] ?? ucfirst($this->statusType);

        $mail = (new MailMessage)
            ->subject('[' . $label . '] Status Pengajuan - ' . $docNo)
            ->greeting('Halo,')
            ->line('Status pengajuan **' . $docNo . '** (' . ($isService ? 'Service Request' : 'Purchase Request') . ') telah diperbarui menjadi **' . $label . '**.');

        if (!empty($this->notes)) {
            $mail->line('**Catatan / Alasan:** ' . $this->notes);
        }

        $url = url('/purchasing-approval?open_pr=' . $this->request->id . '&category=' . $this->type);
        $mail->action('Lihat Pengajuan', $url)
            ->line('Terima kasih.');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $actionMessages = [
            'approved' => 'telah menyetujui pengajuan',
            'rejected' => 'telah menolak pengajuan',
            'reopened' => 'telah membuka kembali (reopen) pengajuan',
        ];
        $msg = $actionMessages[$this->statusType] ?? 'telah memperbarui status';

        return [
            'vendor_name' => 'Tim Purchasing',
            'rfq_id' => null, 
            'pr_id' => $this->request->id,
            'document_number' => $this->request->document_number,
            'category' => $this->type,
            'message' => $msg . ' untuk',
        ];
    }
}
