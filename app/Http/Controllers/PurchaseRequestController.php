<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Rfq;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestJob;
use App\Models\ServiceRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $prQuery = PurchaseRequest::with([
            'items', 'user',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems',
            'rfqs.histories.user',
        ])->latest();

        $srQuery = ServiceRequest::with(['jobs.items', 'user'])->latest();

        if ($user->role !== 'purchasing') {
            $prQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', fn($u) => $u->where('role', 'purchasing'));
            });
            $srQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', fn($u) => $u->where('role', 'purchasing'));
            });
        }

        $prs = $prQuery->get()->map(function ($req) {
            $req->type          = 'goods';
            $req->display_doc   = $req->document_number;
            $req->display_title = $req->title;
            $req->item_count    = $req->items->count();
            return $req;
        });

        $srs = $srQuery->get()->map(function ($req) {
            $req->type          = 'service';
            $req->display_doc   = $req->document_number ?? ('SR-' . now()->format('Y') . '-' . str_pad($req->id, 4, '0', STR_PAD_LEFT));
            $req->display_title = $req->service_name;
            $itemCount = 0;
            foreach ($req->jobs as $job) { $itemCount += $job->items->count(); }
            $req->item_count = $itemCount;
            return $req;
        });

        $allRequests  = $prs->concat($srs)->sortByDesc('created_at')->values();
        $isPurchasing = $user->role === 'purchasing';

        return view('purchase_requests.list', compact('allRequests', 'isPurchasing'));
    }

    public function create()
    {
        // Goods catalog — from Master Items table
        $existingItems = \App\Models\Item::where('is_archived', false)
            ->orderBy('item_name')
            ->get()->map(function ($item) {
                return [
                    'id'    => $item->item_code ?? 'ITM-' . $item->id,
                    'name'  => $item->item_name,
                    'unit'  => $item->unit,
                    'spec'  => $item->specification ?? '',
                    'notes' => $item->item_notes ?? '',
                ];
            })->values();

        // Service templates — built from past ServiceRequests in DB
        $existingServiceTemplates = ServiceRequest::with('jobs.items')
            ->latest()
            ->get()
            ->unique('service_name')
            ->map(function ($sr) {
                return [
                    'id'           => 'SR-' . $sr->id,
                    'service_name' => $sr->service_name,
                    'doc_number'   => $sr->document_number ?? $sr->display_doc,
                    'jobs'         => $sr->jobs->map(function ($job) {
                        return [
                            'description' => $job->job_description,
                            'items'       => $job->items->map(function ($item) {
                                return [
                                    'name' => $item->item_name,
                                    'qty'  => $item->quantity,
                                    'unit' => $item->unit,
                                    'spec' => $item->specification ?? '',
                                ];
                            })->values()->toArray(),
                        ];
                    })->values()->toArray(),
                ];
            })->values();

        // Auto-generate next PR document number: PR-YYYY-NNNN
        $prYearCount  = PurchaseRequest::whereYear('created_at', now()->year)->count() + 1;
        $nextPrDocNum = 'PR-' . now()->format('Y') . '-' . str_pad($prYearCount, 4, '0', STR_PAD_LEFT);

        // Auto-generate next SR document number: SR-YYYY-NNNN
        $srYearCount  = ServiceRequest::whereYear('created_at', now()->year)->count() + 1;
        $nextSrDocNum = 'SR-' . now()->format('Y') . '-' . str_pad($srYearCount, 4, '0', STR_PAD_LEFT);

        return view('purchase_requests.create', compact('existingItems', 'existingServiceTemplates', 'nextPrDocNum', 'nextSrDocNum'));
    }

    public function store(Request $request)
    {
        if ($request->item_type === 'service') {
            $request->validate([
                'requested_date' => 'required|date',
                'plant'          => 'required|string',
                'services'       => 'required|array|min:1',
            ]);

            foreach ($request->services as $svcData) {
                // Generate SR document number: SR-YYYY-NNNN
                $srCount  = ServiceRequest::whereYear('created_at', now()->year)->count() + 1;
                $srDocNum = 'SR-' . now()->format('Y') . '-' . str_pad($srCount, 4, '0', STR_PAD_LEFT);

                $sr = ServiceRequest::create([
                    'user_id'         => Auth::id(),
                    'department'      => $request->service_department ?? Auth::user()->department,
                    'document_number' => $srDocNum,
                    'service_name'    => $svcData['service_name'],
                    'submission_date' => now(),
                    'requested_date'  => $request->requested_date,
                    'plant'           => $request->plant,
                    'status'          => 'submitted',
                ]);

                foreach ($svcData['jobs'] as $jobData) {
                    $job = ServiceRequestJob::create([
                        'service_request_id' => $sr->id,
                        'job_description'    => $jobData['description'],
                    ]);

                    foreach ($jobData['items'] as $itemData) {
                        ServiceRequestItem::create([
                            'job_id'        => $job->id,
                            'item_name'     => $itemData['item_name'],
                            'quantity'      => $itemData['quantity'],
                            'unit'          => $itemData['unit'],
                            'specification' => $itemData['specification'] ?? null,
                        ]);
                    }
                }
                $docInfo = 'SR-' . str_pad($sr->id, 4, '0', STR_PAD_LEFT);
            }

            History::create([
                'user_id'            => Auth::id(),
                'action'             => 'Request Created',
                'transaction_status' => 'completed',
                'notes'              => "Dokumen $docInfo berhasil dibuat.",
                'action_date'        => now(),
            ]);

            return redirect()->route('pr.list')->with('success', "Request $docInfo berhasil dibuat.");

        } else {
            $request->validate([
                'title'           => 'required|string',
                'department'      => 'required|string',
                'plant'           => 'required|string',
                'requested_date'  => 'required|date',
                'need_date'       => 'required|date',
                'items'           => 'required|array|min:1',
                'items.*.item_id' => 'required|string',
            ]);

            // Auto-generate PR-YYYY-NNNN (4-digit, same format as SR)
            $prYearCount  = PurchaseRequest::whereYear('created_at', now()->year)->count() + 1;
            $prDocNum     = 'PR-' . now()->format('Y') . '-' . str_pad($prYearCount, 4, '0', STR_PAD_LEFT);

            $pr = PurchaseRequest::create([
                'user_id'         => Auth::id(),
                'document_number' => $prDocNum,
                'title'           => $request->title,
                'department'      => $request->department,
                'plant'           => $request->plant,
                'submission_date' => now(),
                'requested_date'  => $request->requested_date,
                'need_date'       => $request->need_date,
                'status'          => 'submitted',
            ]);

            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'item_id'             => $item['item_id'],
                    'item_name'           => $item['item_name'],
                    'quantity'            => $item['quantity'],
                    'unit'                => $item['unit'],
                    'specification'       => $item['specification'] ?? null,
                    'item_notes'          => $item['item_notes'] ?? null,
                ]);
            }
            $docInfo = $prDocNum;

            History::create([
                'user_id'            => Auth::id(),
                'action'             => 'Request Created',
                'transaction_status' => 'completed',
                'notes'              => "Dokumen $docInfo berhasil dibuat.",
                'action_date'        => now(),
            ]);

            return redirect()->route('pr.list')->with('success', "Request $docInfo berhasil dibuat.");
        }
    }

    public function approve(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:goods,service',
        ]);

        $isService = $request->type === 'service';

        if ($isService) {
            $req = ServiceRequest::findOrFail($request->id);
            $docNum = $req->document_number ?? ('SR-' . str_pad($req->id, 4, '0', STR_PAD_LEFT));
        } else {
            $req = PurchaseRequest::findOrFail($request->id);
            $docNum = $req->document_number;
        }

        if ($req->status !== 'submitted') {
            return back()->with('error', 'Request cannot be approved in its current status.');
        }

        $req->status = 'vendor_search';
        $req->save();

        // Create an RFQ if none exists so Admin can immediately add Quotations
        $rfq = $req->rfqs()->first();
        if (!$rfq) {
            $todayCount = Rfq::whereDate('created_at', today())->count() + 1;
            $rfq = Rfq::create([
                'purchase_request_id' => $isService ? null : $req->id,
                'service_request_id'  => $isService ? $req->id : null,
                'rfq_number'          => 'RFQ-' . now()->format('Y-md') . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT),
                'status'              => 'open',
                'opened_at'           => now(),
            ]);
        }

        History::create([
            'user_id'            => Auth::id(),
            'rfq_id'             => $rfq->id,
            'action'             => 'Request Approved',
            'transaction_status' => 'completed',
            'notes'              => "Dokumen $docNum disetujui dan masuk ke tahap Vendor Search.",
            'action_date'        => now(),
        ]);

        return back()->with('success', "Request $docNum berhasil di-approve. Silakan tambahkan Quotation.");
    }

    public function reject(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:goods,service',
        ]);

        $isService = $request->type === 'service';

        if ($isService) {
            $req = ServiceRequest::findOrFail($request->id);
            $docNum = $req->document_number ?? ('SR-' . str_pad($req->id, 4, '0', STR_PAD_LEFT));
        } else {
            $req = PurchaseRequest::findOrFail($request->id);
            $docNum = $req->document_number;
        }

        if ($req->status !== 'submitted') {
            return back()->with('error', 'Request cannot be rejected in its current status.');
        }

        $req->status = 'rejected';
        $req->save();

        History::create([
            'user_id'            => Auth::id(),
            'action'             => 'Request Rejected',
            'transaction_status' => 'completed',
            'notes'              => "Dokumen $docNum ditolak oleh Admin.",
            'action_date'        => now(),
        ]);

        return back()->with('success', "Request $docNum berhasil ditolak.");
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:goods,service',
        ]);

        $isService = $request->type === 'service';

        if ($isService) {
            $req = ServiceRequest::findOrFail($request->id);
            $docNum = $req->document_number ?? ('SR-' . str_pad($req->id, 4, '0', STR_PAD_LEFT));
        } else {
            $req = PurchaseRequest::findOrFail($request->id);
            $docNum = $req->document_number;
        }

        if (!in_array($req->status, ['vendor_search', 'vendor_selection'])) {
            return back()->with('error', 'Request cannot be cancelled in its current status.');
        }

        $req->status = 'cancelled';
        $req->save();

        History::create([
            'user_id'            => Auth::id(),
            'action'             => 'Request Cancelled',
            'transaction_status' => 'completed',
            'notes'              => "Dokumen $docNum dibatalkan oleh Admin.",
            'action_date'        => now(),
        ]);

        return back()->with('success', "Request $docNum berhasil dibatalkan.");
    }
        public function reopen(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:goods,service',
        ]);

        $isService = $request->type === 'service';

        if ($isService) {
            $req = ServiceRequest::findOrFail($request->id);
            $docNum = $req->document_number ?? ('SR-' . str_pad($req->id, 4, '0', STR_PAD_LEFT));
        } else {
            $req = PurchaseRequest::findOrFail($request->id);
            $docNum = $req->document_number;
        }

        if ($req->status !== 'completed') {
            return back()->with('error', 'Hanya request dengan status Completed yang dapat dikembalikan.');
        }

        // Kembalikan ke tahap vendor_selection
        $req->status = 'vendor_selection';
        $req->save();

        // RE-OPEN RFQ: Agar link token vendor kembali hidup dan bisa diakses
        foreach ($req->rfqs as $rfq) {
            if ($rfq->status === 'closed') {
                $rfq->status = 'open';
                // Opsional: perpanjang token vendor jika sudah expired
                if ($rfq->token_expires_at && $rfq->token_expires_at < now()) {
                    $rfq->token_expires_at = now()->addDays(7);
                }
                $rfq->save();
            }
        }

        History::create([
            'user_id'            => Auth::id(),
            'action'             => 'Request Re-opened',
            'transaction_status' => 'vendor_selection',
            'notes'              => "Dokumen $docNum dikembalikan ke Vendor Selection oleh Admin untuk diperpanjang waktunya.",
            'action_date'        => now(),
        ]);

        return back()->with('success', "Request $docNum berhasil dikembalikan ke tahap Vendor Selection.");
    }

    /**
     * Upload attachment file for a PR (User action).
     */
    public function uploadAttachment(Request $request)
    {
        $request->validate([
            'id'         => 'required|integer',
            'type'       => 'required|in:goods,service',
            'attachment' => 'required|file|mimes:pdf,xlsx,xls,jpg,jpeg,png|max:10240',
        ]);

        $path = $request->file('attachment')->store('pr-attachments', 'public');
        if ($request->type === 'goods') {
            $pr = PurchaseRequest::findOrFail($request->id);
            $pr->update(['attachment_path' => $path]);
        } else {
            $pr = ServiceRequest::findOrFail($request->id);
            $pr->update(['attachment_path' => $path]);
        }

        return back()->with('success', 'Attachment berhasil diunggah.');
    }

    /**
     * Save purchasing notes for a PR (Purchasing role only, JSON response).
     */
    public function savePurchasingNotes(Request $request)
    {
        $data = $request->validate([
            'id'    => 'required|integer',
            'type'  => 'required|in:goods,service',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->type === 'goods') {
            $pr = PurchaseRequest::findOrFail($data['id']);
            $pr->update(['purchasing_notes' => $data['notes'] ?? null]);
        } else {
            $pr = ServiceRequest::findOrFail($data['id']);
            $pr->update(['purchasing_notes' => $data['notes'] ?? null]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Update PR items qty/unit (User action for quotation_reopen status).
     */
    public function updateItems(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|in:goods,service',
        ]);

        if ($request->type === 'goods') {
            $pr = PurchaseRequest::with('items')->findOrFail($request->id);

            if (!in_array($pr->status, ['quotation_reopen', 'submitted', 'vendor_search'])) {
                return back()->withErrors(['error' => 'Editing items is not allowed in current status.']);
            }

            foreach ($pr->items as $item) {
                $newQty  = $request->input("items.{$item->id}.quantity", $item->quantity);
                $newUnit = $request->input("items.{$item->id}.unit", $item->unit);
                $item->update(['quantity' => $newQty, 'unit' => $newUnit]);
            }

            History::create([
                'user_id'            => Auth::id(),
                'action'             => 'PR Items Updated',
                'transaction_status' => $pr->status,
                'notes'              => "Item pada Purchase Request " . $pr->document_number . " telah diperbarui oleh " . Auth::user()->name,
                'action_date'        => now(),
            ]);
        } else {
            $pr = ServiceRequest::with('jobs.items')->findOrFail($request->id);

            if (!in_array($pr->status, ['quotation_reopen', 'submitted', 'vendor_search'])) {
                return back()->withErrors(['error' => 'Editing items is not allowed in current status.']);
            }

            foreach ($pr->jobs as $job) {
                foreach ($job->items as $item) {
                    $newQty  = $request->input("items.{$item->id}.quantity", $item->quantity);
                    $newUnit = $request->input("items.{$item->id}.unit", $item->unit);
                    $item->update(['quantity' => $newQty, 'unit' => $newUnit]);
                }
            }

            History::create([
                'user_id'            => Auth::id(),
                'action'             => 'SR Items Updated',
                'transaction_status' => $pr->status,
                'notes'              => "Item pada Service Request " . $pr->document_number . " telah diperbarui oleh " . Auth::user()->name,
                'action_date'        => now(),
            ]);
        }

        return back()->with('success', 'Items/Services berhasil diperbarui.');
    }
}