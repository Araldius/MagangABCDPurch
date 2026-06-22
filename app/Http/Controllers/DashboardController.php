<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\ServiceRequest;
use App\Models\VendorSelection;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return $user->role === 'purchasing'
            ? $this->purchasingDashboard()
            : $this->userDashboard();
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