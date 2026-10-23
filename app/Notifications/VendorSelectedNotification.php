<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VendorSelectedNotification extends Notification
{
    use Queueable;

    public $request;
    public $type; 

    public function __construct($request, $type)
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

        $mail = (new MailMessage)
            ->subject('[Vendor Selected] Pemilihan Vendor Selesai - ' . $docNo)
            ->greeting('Halo Tim Purchasing & Rekan,')
            ->line('Pemilihan vendor (Vendor Selection) telah berhasil diselesaikan untuk pengajuan **' . $docNo . '** (' . ($isService ? 'Service Request' : 'Purchase Request') . ').')
            ->line('**Pemohon:** ' . $creatorName)
            ->line('**Plant / Departemen:** ' . $plant . ' / ' . $dept);

        // Include winning vendors & item details if available
        $selections = $this->request->vendorSelections;
        if ($selections && $selections->count() > 0) {
            $mail->line('---')
                ->line('**Daftar Vendor Pemenang & Item Terpilih:**');
            
            $grandTotal = 0;
            foreach ($selections as $idx => $sel) {
                $vendorName = $sel->vendor ? $sel->vendor->vendor_name : 'Vendor';
                $items = $sel->selectionItems;
                $vendorTotal = 0;
                
                $mail->line(($idx + 1) . '. **' . $vendorName . '**');
                if ($items && $items->count() > 0) {
                    foreach ($items as $item) {
                        $itemName = $isService 
                            ? ($item->serviceRequestItem->item_name ?? 'Service Item')
                            : ($item->purchaseRequestItem->item_name ?? 'Item');
                        $qty = $item->final_quantity ?? 1;
                        $unit = $item->final_unit ?? 'Unit';
                        $price = (float) ($item->final_price_per_item ?? 0);
                        $subtotal = $qty * $price;
                        $vendorTotal += $subtotal;

                        $mail->line('   • ' . $itemName . ' (' . $qty . ' ' . $unit . ') @ Rp ' . number_format($price, 0, ',', '.') . ' = **Rp ' . number_format($subtotal, 0, ',', '.') . '**');
                    }
                }
                $mail->line('   Subtotal Vendor: **Rp ' . number_format($vendorTotal, 0, ',', '.') . '**');
                $grandTotal += $vendorTotal;
            }
            $mail->line('---')
                ->line('**Grand Total Estimasi PO: Rp ' . number_format($grandTotal, 0, ',', '.') . '**');
        }

        $url = url('/purchasing-approval?open_pr=' . $this->request->id . '&category=' . $this->type);
        $mail->action('Lihat Detail Pengajuan', $url)
            ->line('Status pengajuan ini telah dialihkan ke tahap selanjutnya.')
            ->line('Terima kasih.');

        return $mail;
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