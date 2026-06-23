<?php
namespace App\Http\Controllers;
use App\Models\PurchaseRequest;
use App\Models\ServiceRequest;
use App\Models\VendorSelection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return $user->role === 'purchasing'
            ? $this->purchasingDashboard()
            : $this->userDashboard();
    }
    public function getChartData(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'purchasing') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $filter = $request->query('filter', 'month'); // week, month, year, all
        $query = VendorSelection::with(['vendor', 'selectionItems', 'rfq.purchaseRequest.items'])
            ->whereHas('rfq.purchaseRequest', function ($q) {
                $q->where('status', 'completed');
            });
        // Filter tanggal berdasarkan decided_at
        if ($filter === 'week') {
            $query->where('decided_at', '>=', Carbon::now()->startOfWeek());
        } elseif ($filter === 'month') {
            $query->where('decided_at', '>=', Carbon::now()->startOfMonth());
        } elseif ($filter === 'year') {
            $query->where('decided_at', '>=', Carbon::now()->startOfYear());
        }
        $selections = $query->get();
        // Agregasi data per vendor
        $data = $selections->groupBy('vendor_id')->map(function ($group) {
            $vendor = $group->first()->vendor;
            $vendorName = $vendor->vendor_name ?? $vendor->name ?? 'Unknown';
            
            // Frekuensi terpilih
            $frequency = $group->count();
            
            // Total nilai transaksi (Rupiah)
            $totalValue = $group->reduce(function ($carry, $selection) {
                return $carry + $selection->selectionItems->sum(function ($item) {
                    return ($item->final_price_per_item ?? 0) * ($item->final_quantity ?? 0);
                });
            }, 0);
        // Detail item per vendor — diambil dari order records (PR items, match via purchase_request_item_id)
        $items = $group->flatMap(function ($selection) {
            $prItems     = optional(optional($selection->rfq)->purchaseRequest)->items ?? collect();
            $prItemsById = $prItems->keyBy('id');

            return $selection->selectionItems->map(function ($si) use ($prItemsById) {
                $pri   = $prItemsById->get($si->purchase_request_item_id);
                $qty   = $si->final_quantity ?? 0;
                $price = $si->final_price_per_item ?? 0;
                return [
                    'item_id'     => optional($pri)->item_id ?? optional($pri)->item_code ?? '-',
                    'item_name'   => optional($pri)->item_name ?? '-',
                    'qty'         => $qty,
                    'unit'        => optional($pri)->unit ?? '-',
                    'unit_price'  => $price,
                    'total_price' => $qty * $price,
                ];
            });
        })->values();

        return [
            'vendor_name' => $vendorName,
            'frequency' => $frequency,
            'total_value' => $totalValue,
            'items' => $items
        ];
    })->sortByDesc('frequency')->values();
    return response()->json($data);
}

private function userDashboard()
    {
        $userId = Auth::id();
        $prs = PurchaseRequest::with([
            'items', 'user',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems',
            'rfqs.histories.user',
        ])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('user', fn($u) => $u->where('role', 'purchasing'));
            })
            ->latest()
            ->get()
            ->map(function ($req) {
                $req->type          = 'goods';
                $req->display_doc   = $req->document_number;
                $req->display_title = $req->title;
                $req->item_count    = $req->items->count();
                return $req;
            });

        $srs = ServiceRequest::with(['jobs.items', 'user'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('user', fn($u) => $u->where('role', 'purchasing'));
            })
            ->latest()
            ->get()
            ->map(function ($req) {
                $req->type          = 'service';
                $req->display_doc   = $req->document_number ?? ('SR-' . now()->format('Y') . '-' . str_pad($req->id, 4, '0', STR_PAD_LEFT));
                $req->display_title = $req->service_name;
                $itemCount = 0;
                foreach ($req->jobs as $job) { $itemCount += $job->items->count(); }
                $req->item_count = $itemCount;
                return $req;
            });

        $requests = $prs->concat($srs)->sortByDesc('created_at')->values();
        $activePrs        = $requests->whereNotIn('status', ['completed', 'rejected', 'cancelled'])->count();
        $awaitingApproval = $requests->where('status', 'submitted')->count();
        $inProcess        = $requests->whereIn('status', ['vendor_search', 'vendor_selection'])->count();
        $completedMonth   = $requests->where('status', 'completed')
            ->filter(fn($r) => $r->updated_at->month === now()->month
                            && $r->updated_at->year  === now()->year)
            ->count();
        $recentHistory = VendorSelection::with(['vendor', 'rfq.purchaseRequest', 'selectionItems'])
            ->whereHas('rfq.purchaseRequest', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'completed');
            })
            ->latest('decided_at')
            ->limit(5)
            ->get();
        return view('dashboard.user', compact(
            'requests', 'activePrs', 'awaitingApproval', 'inProcess',
            'completedMonth', 'recentHistory'
        ));
    }

    private function purchasingDashboard()
    {
        $prs = PurchaseRequest::with([
            'items', 'user',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems',
            'rfqs.histories.user',
        ])
            ->latest()
            ->get()
            ->map(function ($req) {
                $req->type          = 'goods';
                $req->display_doc   = $req->document_number;
                $req->display_title = $req->title;
                $req->item_count    = $req->items->count();
                return $req;
            });

        $srs = ServiceRequest::with(['jobs.items', 'user'])
            ->latest()
            ->get()
            ->map(function ($req) {
                $req->type          = 'service';
                $req->display_doc   = $req->document_number ?? ('SR-' . now()->format('Y') . '-' . str_pad($req->id, 4, '0', STR_PAD_LEFT));
                $req->display_title = $req->service_name;
                $itemCount = 0;
                foreach ($req->jobs as $job) { $itemCount += $job->items->count(); }
                $req->item_count = $itemCount;
                return $req;
            });
        $requests = $prs->concat($srs)->sortByDesc('created_at')->values();
        $activePrs        = $requests->count();
        $awaitingApproval = $requests->where('status', 'submitted')->count();
        $inProcess        = $requests->whereIn('status', ['vendor_search', 'vendor_selection'])->count();
        $completedMonth   = $requests->where('status', 'completed')
            ->filter(fn($r) => $r->updated_at->month === now()->month
                            && $r->updated_at->year  === now()->year)
            ->count();

        $recentHistory = VendorSelection::with(['vendor', 'rfq.purchaseRequest', 'selectionItems'])
            ->whereHas('rfq.purchaseRequest', function ($q) {
                $q->where('status', 'completed');
            })
            ->latest('decided_at')
            ->limit(5)
            ->get();

        return view('dashboard.user', compact(
            'requests', 'activePrs', 'awaitingApproval', 'inProcess',
            'completedMonth', 'recentHistory'
        ));
    }
}