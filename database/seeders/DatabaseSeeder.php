<?php
namespace Database\Seeders;
use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestJob;
use App\Models\ServiceRequestItem;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\QuotationPeriod;
use App\Models\QuotationSummary;
use App\Models\Rfq;
use App\Models\SelectionItem;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\VendorSelection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    // ── Auto-increment counters ────────────────────────────────────────────────
    private int $itmSeq = 0; // item_id:  ITM-0001, ITM-0002, ...
    private int $svcSeq = 0; // job_code: SVC-0001, SVC-0002, ...
    private function itm(): string { return 'ITM-' . str_pad(++$this->itmSeq, 4, '0', STR_PAD_LEFT); }
    private function svc(): string { return 'SVC-' . str_pad(++$this->svcSeq, 4, '0', STR_PAD_LEFT); }
    public function run(): void
    {
        // ── Wipe all tables ───────────────────────────────────────────────────
        $tables = [
            'history','selection_items','vendor_selections',
            'quotation_summaries','quotation_details','quotations',
            'quotation_periods','vendor_quotations','rfqs',
            'service_request_items','service_request_jobs','service_requests',
            'purchase_request_items','purchase_requests','vendors','users',
        ];
        try { DB::statement('PRAGMA foreign_keys = OFF'); }
        catch (\Exception $e) { DB::statement('SET FOREIGN_KEY_CHECKS=0'); }
        foreach ($tables as $t) { DB::table($t)->truncate(); }
        try { DB::statement('PRAGMA foreign_keys = ON'); }
        catch (\Exception $e) { DB::statement('SET FOREIGN_KEY_CHECKS=1'); }
        // ════════════════════════════════════════════════════════════════════
        // 1. USERS
        // ════════════════════════════════════════════════════════════════════
        $admin = User::create([
            'name' => 'Admin Purchasing', 'email' => 'admin@company.com',
            'password' => Hash::make('password'), 'role' => 'purchasing', 'department' => 'Purchasing',
        ]);
        $user = User::create([
            'name' => 'John Requester', 'email' => 'user@company.com',
            'password' => Hash::make('password'), 'role' => 'user', 'department' => 'Operations',
        ]);
        $user2 = User::create([
            'name' => 'Budi Santoso', 'email' => 'budi@company.com',
            'password' => Hash::make('password'), 'role' => 'user', 'department' => 'Maintenance',
        ]);
        // ════════════════════════════════════════════════════════════════════
        // 2. VENDORS (7 active vendors)
        // ════════════════════════════════════════════════════════════════════
        $v1 = Vendor::create(['vendor_name' => 'PT Tekno Mandiri',    'location' => 'Jakarta',  'contact' => 'sales@teknomandiri.co.id', 'status' => 'active']);
        $v2 = Vendor::create(['vendor_name' => 'CV Maju Komputer',    'location' => 'Bandung',  'contact' => 'info@majukomputer.com', 'status' => 'active']);
        $v3 = Vendor::create(['vendor_name' => 'PT Karya Jasa',       'location' => 'Surabaya', 'contact' => 'contact@karyajasa.co.id', 'status' => 'active']);
        $v4 = Vendor::create(['vendor_name' => 'CV Bangun Nusantara', 'location' => 'Semarang', 'contact' => 'hello@bangunnusantara.com', 'status' => 'active']);
        $v5 = Vendor::create(['vendor_name' => 'PT Sumber Makmur',    'location' => 'Cikarang', 'contact' => 'sales@sumbermakmur.co.id', 'status' => 'active']);
        $v6 = Vendor::create(['vendor_name' => 'CV Delta Sejahtera',  'location' => 'Bekasi',   'contact' => 'info@deltasejahtera.com', 'status' => 'active']);
        $v7 = Vendor::create(['vendor_name' => 'PT Arindo Perkasa',   'location' => 'Gresik',   'contact' => 'contact@arindoperkasa.co.id', 'status' => 'active']);
        // ════════════════════════════════════════════════════════════════════
        // ── PURCHASE REQUESTS (GOODS) ────────────────────────────────────
        // ════════════════════════════════════════════════════════════════════
        // ── PR-01: COMPLETED — Pengadaan Perangkat IT (Split PO: Laptop→v1, Mouse→v2) ──
        $pr1 = PurchaseRequest::create([
            'user_id' => $user->id, 'document_number' => 'PR-2026-0001',
            'title' => 'Pengadaan Perangkat IT Baru', 'department' => 'Operations',
            'priority' => 'high', 'plant' => 'Cikarang',
            'submission_date' => now()->subDays(30), 'requested_date' => now()->subDays(29),
            'need_date' => now()->addDays(5), 'note' => 'Untuk karyawan baru batch Q2',
            'status' => 'completed',
        ]);
        $pr1_i1 = PurchaseRequestItem::create(['purchase_request_id' => $pr1->id, 'item_id' => $this->itm(), 'item_name' => 'Laptop Thinkpad E15',      'quantity' => 2, 'unit' => 'Unit',   'specification' => 'Core i7, 16GB RAM',           'item_notes' => 'Untuk engineer baru']);
        $pr1_i2 = PurchaseRequestItem::create(['purchase_request_id' => $pr1->id, 'item_id' => $this->itm(), 'item_name' => 'Mouse Wireless Logitech',   'quantity' => 5, 'unit' => 'Pcs',    'specification' => 'M220 Silent',                 'item_notes' => '']);
        $rfq1 = Rfq::create(['purchase_request_id' => $pr1->id, 'rfq_number' => 'RFQ-2026-0101', 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(25), 'status' => 'closed', 'opened_at' => now()->subDays(28), 'closed_at' => now()->subDays(26), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq1->id, 'round' => 1, 'start_date' => now()->subDays(28), 'end_date' => now()->subDays(26), 'status' => 'closed']);
        VendorQuotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'status' => 'submitted', 'submitted_at' => now()->subDays(27)]);
        $q1v1    = Quotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'total_price' => 30500000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd1v1i1 = QuotationDetail::create(['quotation_id' => $q1v1->id, 'purchase_request_item_id' => $pr1_i1->id, 'offered_price_per_item' => 15000000, 'offered_quantity' => 2, 'offered_unit' => 'Pcs']);
        $qd1v1i2 = QuotationDetail::create(['quotation_id' => $q1v1->id, 'purchase_request_item_id' => $pr1_i2->id, 'offered_price_per_item' => 100000,   'offered_quantity' => 5, 'offered_unit' => 'Pcs']);
        $qs1v1i1 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1v1i1->id, 'is_sent_to_user' => true]);
        $qs1v1i2 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1v1i2->id, 'is_sent_to_user' => true]);
        VendorQuotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v2->id, 'status' => 'submitted', 'submitted_at' => now()->subDays(27)]);
        $q1v2    = Quotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v2->id, 'total_price' => 32400000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd1v2i1 = QuotationDetail::create(['quotation_id' => $q1v2->id, 'purchase_request_item_id' => $pr1_i1->id, 'offered_price_per_item' => 16000000, 'offered_quantity' => 2, 'offered_unit' => 'Pcs']);
        $qd1v2i2 = QuotationDetail::create(['quotation_id' => $q1v2->id, 'purchase_request_item_id' => $pr1_i2->id, 'offered_price_per_item' => 80000,    'offered_quantity' => 5, 'offered_unit' => 'Pcs']);
        $qs1v2i1 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1v2i1->id, 'is_sent_to_user' => true]);
        $qs1v2i2 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1v2i2->id, 'is_sent_to_user' => true]);
        // Split PO: Laptop→v1 (lebih murah), Mouse→v2 (lebih murah)
        $sel1v1 = VendorSelection::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'quotation_id' => $q1v1->id, 'decision_notes' => 'V1 lebih murah untuk laptop',  'decided_at' => now()->subDays(24)]);
        SelectionItem::create(['vendor_selection_id' => $sel1v1->id, 'quotation_summary_id' => $qs1v1i1->id, 'purchase_request_item_id' => $pr1_i1->id, 'final_price_per_item' => 15000000, 'final_quantity' => 2, 'notes' => 'Best price laptop']);
        $sel1v2 = VendorSelection::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v2->id, 'quotation_id' => $q1v2->id, 'decision_notes' => 'V2 lebih murah untuk mouse',  'decided_at' => now()->subDays(24)]);
        SelectionItem::create(['vendor_selection_id' => $sel1v2->id, 'quotation_summary_id' => $qs1v2i2->id, 'purchase_request_item_id' => $pr1_i2->id, 'final_price_per_item' => 80000,    'final_quantity' => 5, 'notes' => 'Best price mouse']);
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq1->id, 'vendor_selection_id' => $sel1v1->id, 'action' => 'Vendor Selection Submitted', 'transaction_status' => 'completed', 'notes' => 'Split PO: Laptop→PT Tekno Mandiri, Mouse→CV Maju Komputer', 'action_date' => now()->subDays(24)]);
        // ── PR-02: IN_PROCESS — Bahan Kimia Lab (3 vendor, komparasi harga + shortage) ──
        $pr2 = PurchaseRequest::create([
            'user_id' => $user->id, 'document_number' => 'PR-2026-0002',
            'title' => 'Pengadaan Bahan Kimia Lab', 'department' => 'Quality Control',
            'priority' => 'normal', 'plant' => 'Cikarang',
            'submission_date' => now()->subDays(15), 'requested_date' => now()->subDays(14),
            'need_date' => now()->addDays(7), 'note' => 'Untuk analisa kualitas batch Q3',
            'status' => 'vendor_selection',
        ]);
        $pr2_i1 = PurchaseRequestItem::create(['purchase_request_id' => $pr2->id, 'item_id' => $this->itm(), 'item_name' => 'Asam Sulfat 98%',          'quantity' => 50,  'unit' => 'Liter', 'specification' => 'Grade Industri, sertifikasi COA']);
        $pr2_i2 = PurchaseRequestItem::create(['purchase_request_id' => $pr2->id, 'item_id' => $this->itm(), 'item_name' => 'Natrium Hidroksida (NaOH)', 'quantity' => 30,  'unit' => 'Kg',    'specification' => 'Purity ≥97%']);
        $pr2_i3 = PurchaseRequestItem::create(['purchase_request_id' => $pr2->id, 'item_id' => $this->itm(), 'item_name' => 'Indikator pH Universal',    'quantity' => 10,  'unit' => 'Pak',   'specification' => 'Range 0-14, isi 100 strip']);
        $rfq2 = Rfq::create(['purchase_request_id' => $pr2->id, 'rfq_number' => 'RFQ-2026-0102', 'is_sent_to_user' => false, 'status' => 'open', 'opened_at' => now()->subDays(10), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq2->id, 'round' => 1, 'start_date' => now()->subDays(10), 'end_date' => now()->addDays(4), 'status' => 'open']);
        // v1: semua item, harga medium
        $q2v1 = Quotation::create(['rfq_id' => $rfq2->id, 'vendor_id' => $v1->id, 'total_price' => 3250000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        QuotationDetail::create(['quotation_id' => $q2v1->id, 'purchase_request_item_id' => $pr2_i1->id, 'offered_price_per_item' => 45000,  'offered_quantity' => 50, 'offered_unit' => 'Pcs']);
        QuotationDetail::create(['quotation_id' => $q2v1->id, 'purchase_request_item_id' => $pr2_i2->id, 'offered_price_per_item' => 55000,  'offered_quantity' => 30, 'offered_unit' => 'Pcs']);
        QuotationDetail::create(['quotation_id' => $q2v1->id, 'purchase_request_item_id' => $pr2_i3->id, 'offered_price_per_item' => 120000, 'offered_quantity' => 10, 'offered_unit' => 'Pcs']);
        // v5: H2SO4 paling murah, NaOH mahal, tidak ada pH strip
        $q2v5 = Quotation::create(['rfq_id' => $rfq2->id, 'vendor_id' => $v5->id, 'total_price' => 2900000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        QuotationDetail::create(['quotation_id' => $q2v5->id, 'purchase_request_item_id' => $pr2_i1->id, 'offered_price_per_item' => 38000, 'offered_quantity' => 50, 'offered_unit' => 'Pcs']); // CHEAPEST
        QuotationDetail::create(['quotation_id' => $q2v5->id, 'purchase_request_item_id' => $pr2_i2->id, 'offered_price_per_item' => 62000, 'offered_quantity' => 30, 'offered_unit' => 'Pcs']); // EXPENSIVE
        // v6: NaOH & pH paling murah, H2SO4 shortage qty 30 dari 50
        $q2v6 = Quotation::create(['rfq_id' => $rfq2->id, 'vendor_id' => $v6->id, 'total_price' => 3050000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        QuotationDetail::create(['quotation_id' => $q2v6->id, 'purchase_request_item_id' => $pr2_i1->id, 'offered_price_per_item' => 42000,  'offered_quantity' => 30, 'offered_unit' => 'Pcs']); // SHORTAGE!
        QuotationDetail::create(['quotation_id' => $q2v6->id, 'purchase_request_item_id' => $pr2_i2->id, 'offered_price_per_item' => 48000,  'offered_quantity' => 30, 'offered_unit' => 'Pcs']); // CHEAPEST
        QuotationDetail::create(['quotation_id' => $q2v6->id, 'purchase_request_item_id' => $pr2_i3->id, 'offered_price_per_item' => 105000, 'offered_quantity' => 10, 'offered_unit' => 'Pcs']); // CHEAPEST pH
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq2->id, 'action' => 'RFQ Created', 'transaction_status' => 'completed', 'notes' => 'RFQ-2026-0102 dibuat untuk PR-2026-0002', 'action_date' => now()->subDays(10)]);
        // ── PR-03: IN_PROCESS — Peralatan Safety (4 item, 3 vendor, shortage harness) ──
        $pr3 = PurchaseRequest::create([
            'user_id' => $user2->id, 'document_number' => 'PR-2026-0003',
            'title' => 'Pengadaan Peralatan Safety', 'department' => 'HSE',
            'priority' => 'high', 'plant' => 'Gresik',
            'submission_date' => now()->subDays(12), 'requested_date' => now()->subDays(11),
            'need_date' => now()->addDays(3), 'note' => 'Mandatory safety compliance audit',
            'status' => 'vendor_selection',
        ]);
        $pr3_i1 = PurchaseRequestItem::create(['purchase_request_id' => $pr3->id, 'item_id' => $this->itm(), 'item_name' => 'Helm Safety Full Face',    'quantity' => 20,  'unit' => 'Pcs',   'specification' => 'ANSI Z89.1, warna putih']);
        $pr3_i2 = PurchaseRequestItem::create(['purchase_request_id' => $pr3->id, 'item_id' => $this->itm(), 'item_name' => 'Sepatu Safety Steel Toe',  'quantity' => 15,  'unit' => 'Pasang','specification' => 'ISO 20345, ukuran 40-44']);
        $pr3_i3 = PurchaseRequestItem::create(['purchase_request_id' => $pr3->id, 'item_id' => $this->itm(), 'item_name' => 'Safety Harness Full Body', 'quantity' => 10,  'unit' => 'Set',   'specification' => 'EN361, load 100kg']);
        $pr3_i4 = PurchaseRequestItem::create(['purchase_request_id' => $pr3->id, 'item_id' => $this->itm(), 'item_name' => 'Respirator Masker N95',    'quantity' => 100, 'unit' => 'Pcs',   'specification' => 'NIOSH certified']);
        $rfq3 = Rfq::create(['purchase_request_id' => $pr3->id, 'rfq_number' => 'RFQ-2026-0103', 'is_sent_to_user' => false, 'status' => 'open', 'opened_at' => now()->subDays(8), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq3->id, 'round' => 1, 'start_date' => now()->subDays(8), 'end_date' => now()->addDays(6), 'status' => 'open']);
        // v3: semua item, stok lengkap, harga tinggi
        $q3v3 = Quotation::create(['rfq_id' => $rfq3->id, 'vendor_id' => $v3->id, 'total_price' => 16500000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        QuotationDetail::create(['quotation_id' => $q3v3->id, 'purchase_request_item_id' => $pr3_i1->id, 'offered_price_per_item' => 185000, 'offered_quantity' => 20, 'offered_unit' => 'Pcs']);
        QuotationDetail::create(['quotation_id' => $q3v3->id, 'purchase_request_item_id' => $pr3_i2->id, 'offered_price_per_item' => 350000, 'offered_quantity' => 15, 'offered_unit' => 'Pcs']);
        QuotationDetail::create(['quotation_id' => $q3v3->id, 'purchase_request_item_id' => $pr3_i3->id, 'offered_price_per_item' => 650000, 'offered_quantity' => 10, 'offered_unit' => 'Pcs']);
        QuotationDetail::create(['quotation_id' => $q3v3->id, 'purchase_request_item_id' => $pr3_i4->id, 'offered_price_per_item' => 22000,  'offered_quantity' => 100, 'offered_unit' => 'Pcs']);
        // v7: harga murah tapi harness hanya 7 (shortage)
        $q3v7 = Quotation::create(['rfq_id' => $rfq3->id, 'vendor_id' => $v7->id, 'total_price' => 14200000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        QuotationDetail::create(['quotation_id' => $q3v7->id, 'purchase_request_item_id' => $pr3_i1->id, 'offered_price_per_item' => 160000, 'offered_quantity' => 20, 'offered_unit' => 'Pcs']); // CHEAPEST helm
        QuotationDetail::create(['quotation_id' => $q3v7->id, 'purchase_request_item_id' => $pr3_i2->id, 'offered_price_per_item' => 320000, 'offered_quantity' => 15, 'offered_unit' => 'Pcs']); // CHEAPEST sepatu
        QuotationDetail::create(['quotation_id' => $q3v7->id, 'purchase_request_item_id' => $pr3_i3->id, 'offered_price_per_item' => 590000, 'offered_quantity' => 7, 'offered_unit' => 'Pcs']);  // SHORTAGE harness!
        QuotationDetail::create(['quotation_id' => $q3v7->id, 'purchase_request_item_id' => $pr3_i4->id, 'offered_price_per_item' => 18000,  'offered_quantity' => 100, 'offered_unit' => 'Pcs']); // CHEAPEST masker
        // v4: hanya helm & masker, harga paling murah tapi tidak lengkap
        $q3v4 = Quotation::create(['rfq_id' => $rfq3->id, 'vendor_id' => $v4->id, 'total_price' => 4750000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        QuotationDetail::create(['quotation_id' => $q3v4->id, 'purchase_request_item_id' => $pr3_i1->id, 'offered_price_per_item' => 145000, 'offered_quantity' => 20, 'offered_unit' => 'Pcs']); // CHEAPEST helm
        QuotationDetail::create(['quotation_id' => $q3v4->id, 'purchase_request_item_id' => $pr3_i4->id, 'offered_price_per_item' => 16000,  'offered_quantity' => 100, 'offered_unit' => 'Pcs']); // CHEAPEST masker
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq3->id, 'action' => 'RFQ Created', 'transaction_status' => 'completed', 'notes' => 'RFQ-2026-0103 dibuat untuk PR-2026-0003', 'action_date' => now()->subDays(8)]);
        // ── PR-04: APPROVED — Komponen Elektronik (1 vendor, VS done) ──
        $pr4 = PurchaseRequest::create([
            'user_id' => $user->id, 'document_number' => 'PR-2026-0004',
            'title' => 'Restock Komponen Elektronik', 'department' => 'Engineering',
            'priority' => 'normal', 'plant' => 'Cibitung',
            'submission_date' => now()->subDays(20), 'requested_date' => now()->subDays(19),
            'need_date' => now()->addDays(2), 'note' => 'Stok PCB dan sensor menipis',
            'status' => 'completed',
        ]);
        $pr4_i1 = PurchaseRequestItem::create(['purchase_request_id' => $pr4->id, 'item_id' => $this->itm(), 'item_name' => 'PCB Arduino Mega 2560',  'quantity' => 10, 'unit' => 'Pcs', 'specification' => 'Original, CH340G chip']);
        $pr4_i2 = PurchaseRequestItem::create(['purchase_request_id' => $pr4->id, 'item_id' => $this->itm(), 'item_name' => 'Sensor Suhu DS18B20',    'quantity' => 20, 'unit' => 'Pcs', 'specification' => 'Waterproof, 1-Wire protocol']);
        $pr4_i3 = PurchaseRequestItem::create(['purchase_request_id' => $pr4->id, 'item_id' => $this->itm(), 'item_name' => 'Kabel Data USB-B 1.5m',  'quantity' => 15, 'unit' => 'Pcs', 'specification' => 'Shielded, panjang 1.5m']);
        $rfq4 = Rfq::create(['purchase_request_id' => $pr4->id, 'rfq_number' => 'RFQ-2026-0104', 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(12), 'status' => 'closed', 'opened_at' => now()->subDays(18), 'closed_at' => now()->subDays(10), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq4->id, 'round' => 1, 'start_date' => now()->subDays(18), 'end_date' => now()->subDays(10), 'status' => 'closed']);
        $q4v2    = Quotation::create(['rfq_id' => $rfq4->id, 'vendor_id' => $v2->id, 'total_price' => 1850000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd4v2i1 = QuotationDetail::create(['quotation_id' => $q4v2->id, 'purchase_request_item_id' => $pr4_i1->id, 'offered_price_per_item' => 95000, 'offered_quantity' => 10, 'offered_unit' => 'Pcs']);
        $qd4v2i2 = QuotationDetail::create(['quotation_id' => $q4v2->id, 'purchase_request_item_id' => $pr4_i2->id, 'offered_price_per_item' => 45000, 'offered_quantity' => 20, 'offered_unit' => 'Pcs']);
        $qd4v2i3 = QuotationDetail::create(['quotation_id' => $q4v2->id, 'purchase_request_item_id' => $pr4_i3->id, 'offered_price_per_item' => 25000, 'offered_quantity' => 15, 'offered_unit' => 'Pcs']);
        $qs4v2i1 = QuotationSummary::create(['rfq_id' => $rfq4->id, 'quotation_detail_id' => $qd4v2i1->id, 'is_sent_to_user' => true]);
        $qs4v2i2 = QuotationSummary::create(['rfq_id' => $rfq4->id, 'quotation_detail_id' => $qd4v2i2->id, 'is_sent_to_user' => true]);
        $qs4v2i3 = QuotationSummary::create(['rfq_id' => $rfq4->id, 'quotation_detail_id' => $qd4v2i3->id, 'is_sent_to_user' => true]);
        $sel4v2  = VendorSelection::create(['rfq_id' => $rfq4->id, 'vendor_id' => $v2->id, 'quotation_id' => $q4v2->id, 'decision_notes' => 'Harga kompetitif, stok lengkap', 'decided_at' => now()->subDays(9)]);
        SelectionItem::create(['vendor_selection_id' => $sel4v2->id, 'quotation_summary_id' => $qs4v2i1->id, 'purchase_request_item_id' => $pr4_i1->id, 'final_price_per_item' => 95000, 'final_quantity' => 10]);
        SelectionItem::create(['vendor_selection_id' => $sel4v2->id, 'quotation_summary_id' => $qs4v2i2->id, 'purchase_request_item_id' => $pr4_i2->id, 'final_price_per_item' => 45000, 'final_quantity' => 20]);
        SelectionItem::create(['vendor_selection_id' => $sel4v2->id, 'quotation_summary_id' => $qs4v2i3->id, 'purchase_request_item_id' => $pr4_i3->id, 'final_price_per_item' => 25000, 'final_quantity' => 15]);
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq4->id, 'vendor_selection_id' => $sel4v2->id, 'action' => 'Vendor Selection Submitted', 'transaction_status' => 'completed', 'notes' => 'Semua item dari CV Maju Komputer', 'action_date' => now()->subDays(9)]);
        // ── PR-05: AWAITING_APPROVAL — Spare Part Mesin CNC ──
        $pr5 = PurchaseRequest::create([
            'user_id' => $user2->id, 'document_number' => 'PR-2026-0005',
            'title' => 'Pengadaan Spare Part Mesin CNC', 'department' => 'Maintenance',
            'priority' => 'high', 'plant' => 'Gresik',
            'submission_date' => now()->subDays(3), 'requested_date' => now()->subDays(2),
            'need_date' => now()->addDays(14), 'note' => 'Mesin CNC-03 breakdown, spare part darurat',
            'status' => 'submitted',
        ]);
        PurchaseRequestItem::create(['purchase_request_id' => $pr5->id, 'item_id' => $this->itm(), 'item_name' => 'Bearing SKF 6205-2RS',   'quantity' => 8, 'unit' => 'Pcs',   'specification' => 'Inner Dia 25mm, 52×15mm']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr5->id, 'item_id' => $this->itm(), 'item_name' => 'V-Belt A-48',            'quantity' => 4, 'unit' => 'Pcs',   'specification' => 'Panjang 48 inch, lebar 13mm']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr5->id, 'item_id' => $this->itm(), 'item_name' => 'Oil Seal TC 25x42x7',   'quantity' => 6, 'unit' => 'Pcs',   'specification' => 'Nitrile rubber, double lip']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr5->id, 'item_id' => $this->itm(), 'item_name' => 'Relay Omron MY4N 24VDC','quantity' => 5, 'unit' => 'Pcs',   'specification' => '4 Pole, 5A']);
        // ── PR-06: AWAITING_APPROVAL — Seragam Karyawan ──
        $pr6 = PurchaseRequest::create([
            'user_id' => $user->id, 'document_number' => 'PR-2026-0006',
            'title' => 'Pengadaan Seragam Karyawan Baru', 'department' => 'HR',
            'priority' => 'low', 'plant' => 'Cibitung',
            'submission_date' => now()->subDays(2), 'requested_date' => now()->subDays(1),
            'need_date' => now()->addDays(21), 'note' => 'Seragam batch karyawan baru 30 orang',
            'status' => 'submitted',
        ]);
        PurchaseRequestItem::create(['purchase_request_id' => $pr6->id, 'item_id' => $this->itm(), 'item_name' => 'Kemeja Seragam Lengan Panjang', 'quantity' => 60, 'unit' => 'Pcs', 'specification' => 'Warna biru navy, bahan katun']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr6->id, 'item_id' => $this->itm(), 'item_name' => 'Celana Seragam Formal',         'quantity' => 60, 'unit' => 'Pcs', 'specification' => 'Warna abu-abu, bahan drill']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr6->id, 'item_id' => $this->itm(), 'item_name' => 'ID Card Holder Lanyard',        'quantity' => 30, 'unit' => 'Pcs', 'specification' => 'Logo perusahaan, anti-breakaway']);
        // ── PR-07: IN_PROCESS — Pelumas Industri (4 vendor, split best-price) ──
        $pr7 = PurchaseRequest::create([
            'user_id' => $user2->id, 'document_number' => 'PR-2026-0007',
            'title' => 'Pengadaan Pelumas Industri', 'department' => 'Maintenance',
            'priority' => 'normal', 'plant' => 'Cibitung',
            'submission_date' => now()->subDays(9), 'requested_date' => now()->subDays(8),
            'need_date' => now()->addDays(5), 'note' => 'Stok pelumas mesin produksi habis',
            'status' => 'vendor_selection',
        ]);
        $pr7_i1 = PurchaseRequestItem::create(['purchase_request_id' => $pr7->id, 'item_id' => $this->itm(), 'item_name' => 'Oli Mesin SAE 40 Industri',  'quantity' => 200, 'unit' => 'Liter', 'specification' => 'API CF-4, viscosity index 120']);
        $pr7_i2 = PurchaseRequestItem::create(['purchase_request_id' => $pr7->id, 'item_id' => $this->itm(), 'item_name' => 'Grease Shell Alvania EP2',   'quantity' => 50,  'unit' => 'Kg',    'specification' => 'NLGI #2, lithium base']);
        $rfq7 = Rfq::create(['purchase_request_id' => $pr7->id, 'rfq_number' => 'RFQ-2026-0107', 'is_sent_to_user' => false, 'status' => 'open', 'opened_at' => now()->subDays(6), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq7->id, 'round' => 1, 'start_date' => now()->subDays(6), 'end_date' => now()->addDays(8), 'status' => 'open']);
        $q7v3 = Quotation::create(['rfq_id' => $rfq7->id, 'vendor_id' => $v3->id, 'total_price' => 13500000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        QuotationDetail::create(['quotation_id' => $q7v3->id, 'purchase_request_item_id' => $pr7_i1->id, 'offered_price_per_item' => 48000, 'offered_quantity' => 200, 'offered_unit' => 'Pcs']);
        QuotationDetail::create(['quotation_id' => $q7v3->id, 'purchase_request_item_id' => $pr7_i2->id, 'offered_price_per_item' => 75000, 'offered_quantity' => 50, 'offered_unit' => 'Pcs']);
        $q7v5 = Quotation::create(['rfq_id' => $rfq7->id, 'vendor_id' => $v5->id, 'total_price' => 11000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        QuotationDetail::create(['quotation_id' => $q7v5->id, 'purchase_request_item_id' => $pr7_i1->id, 'offered_price_per_item' => 41000, 'offered_quantity' => 200, 'offered_unit' => 'Pcs']); // CHEAPEST oli
        QuotationDetail::create(['quotation_id' => $q7v5->id, 'purchase_request_item_id' => $pr7_i2->id, 'offered_price_per_item' => 78000, 'offered_quantity' => 50, 'offered_unit' => 'Pcs']);
        $q7v6 = Quotation::create(['rfq_id' => $rfq7->id, 'vendor_id' => $v6->id, 'total_price' => 12400000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        QuotationDetail::create(['quotation_id' => $q7v6->id, 'purchase_request_item_id' => $pr7_i1->id, 'offered_price_per_item' => 44000, 'offered_quantity' => 200, 'offered_unit' => 'Pcs']);
        QuotationDetail::create(['quotation_id' => $q7v6->id, 'purchase_request_item_id' => $pr7_i2->id, 'offered_price_per_item' => 68000, 'offered_quantity' => 50, 'offered_unit' => 'Pcs']); // CHEAPEST grease
        $q7v7 = Quotation::create(['rfq_id' => $rfq7->id, 'vendor_id' => $v7->id, 'total_price' => 10500000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        QuotationDetail::create(['quotation_id' => $q7v7->id, 'purchase_request_item_id' => $pr7_i1->id, 'offered_price_per_item' => 43000, 'offered_quantity' => 150, 'offered_unit' => 'Pcs']); // SHORTAGE
        QuotationDetail::create(['quotation_id' => $q7v7->id, 'purchase_request_item_id' => $pr7_i2->id, 'offered_price_per_item' => 70000, 'offered_quantity' => 50, 'offered_unit' => 'Pcs']);
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq7->id, 'action' => 'RFQ Created', 'transaction_status' => 'completed', 'notes' => 'RFQ-2026-0107 dibuat untuk PR-2026-0007', 'action_date' => now()->subDays(6)]);
        // ── PR-08: AWAITING_APPROVAL — Packaging Material ──
        $pr8 = PurchaseRequest::create([
            'user_id' => $user->id, 'document_number' => 'PR-2026-0008',
            'title' => 'Pengadaan Packaging Material', 'department' => 'Warehouse',
            'priority' => 'normal', 'plant' => 'Gresik',
            'submission_date' => now()->subDays(1), 'requested_date' => now(),
            'need_date' => now()->addDays(10), 'note' => 'Persiapan shipment Q3',
            'status' => 'submitted',
        ]);
        PurchaseRequestItem::create(['purchase_request_id' => $pr8->id, 'item_id' => $this->itm(), 'item_name' => 'Kardus Box Double Wall 60×40×40', 'quantity' => 500, 'unit' => 'Pcs', 'specification' => 'Kraft paper, pre-printed logo']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr8->id, 'item_id' => $this->itm(), 'item_name' => 'Bubble Wrap Rol 1.2m',            'quantity' => 20,  'unit' => 'Rol', 'specification' => 'Small bubble 10mm, panjang 50m']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr8->id, 'item_id' => $this->itm(), 'item_name' => 'Strapping Band PP 12mm',          'quantity' => 30,  'unit' => 'Rol', 'specification' => 'Lebar 12mm, tebal 0.6mm, 200m/rol']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr8->id, 'item_id' => $this->itm(), 'item_name' => 'Pallet Kayu EUR 120×80cm',         'quantity' => 50,  'unit' => 'Pcs', 'specification' => 'Fumigated ISPM 15']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr8->id, 'item_id' => $this->itm(), 'item_name' => 'Lakban OPP Coklat 48mm',           'quantity' => 100, 'unit' => 'Rol', 'specification' => 'Tebal 50 mikron, 100m/rol']);
        // ── PR-09: AWAITING_APPROVAL — Filter Udara Compressor ──
        $pr9 = PurchaseRequest::create([
            'user_id' => $user2->id, 'document_number' => 'PR-2026-0009',
            'title' => 'Pengadaan Filter Udara Compressor', 'department' => 'Maintenance',
            'priority' => 'high', 'plant' => 'Cikarang',
            'submission_date' => now()->subDays(1), 'requested_date' => now(),
            'need_date' => now()->addDays(5), 'note' => 'Maintenance rutin compressor unit A dan B',
            'status' => 'submitted',
        ]);
        PurchaseRequestItem::create(['purchase_request_id' => $pr9->id, 'item_id' => $this->itm(), 'item_name' => 'Air Filter Element Atlas Copco',    'quantity' => 6, 'unit' => 'Pcs', 'specification' => 'GA37-75 series, OEM 1619127900']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr9->id, 'item_id' => $this->itm(), 'item_name' => 'Oil Filter Separator Cartridge',     'quantity' => 6, 'unit' => 'Pcs', 'specification' => 'GA55-75 series, coalescence type']);
        // ── PR-10: REJECTED — Furnitur Kantor ──
        $pr10 = PurchaseRequest::create([
            'user_id' => $user->id, 'document_number' => 'PR-2026-0010',
            'title' => 'Pengadaan Furnitur Kantor Baru', 'department' => 'General Affairs',
            'priority' => 'low', 'plant' => 'Cikarang',
            'submission_date' => now()->subDays(8), 'requested_date' => now()->subDays(7),
            'need_date' => now()->addDays(30), 'note' => 'Renovasi ruang meeting lantai 3',
            'status' => 'rejected',
        ]);
        PurchaseRequestItem::create(['purchase_request_id' => $pr10->id, 'item_id' => $this->itm(), 'item_name' => 'Meja Rapat Premium Oval',    'quantity' => 1,  'unit' => 'Unit', 'specification' => 'Kayu jati, 300×120cm, 12 kursi']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr10->id, 'item_id' => $this->itm(), 'item_name' => 'Kursi Rapat Executive',      'quantity' => 12, 'unit' => 'Unit', 'specification' => 'Kulit sintetis, armrest, swivel']);
        PurchaseRequestItem::create(['purchase_request_id' => $pr10->id, 'item_id' => $this->itm(), 'item_name' => 'Layar Proyektor 120 inch',   'quantity' => 1,  'unit' => 'Unit', 'specification' => 'Electric motorized, 16:9 ratio']);
        History::create(['user_id' => $admin->id, 'action' => 'Request Rejected', 'transaction_status' => 'completed', 'notes' => 'PR-2026-0010 ditolak: anggaran sudah habis untuk Q2', 'action_date' => now()->subDays(6)]);
        // ════════════════════════════════════════════════════════════════════
        // ── SERVICE REQUESTS ─────────────────────────────────────────────
        // ════════════════════════════════════════════════════════════════════
        // ── SR-01: IN_PROCESS — Renovasi Pantry (2 jobs, 3 vendor offering) ──
        $sr1 = ServiceRequest::create([
            'user_id' => $user->id, 'department' => 'Operations', 'document_number' => 'SR-2026-0001',
            'service_name' => 'Renovasi Pantry Gedung A',
            'submission_date' => now()->subDays(5), 'requested_date' => now()->subDays(4),
            'plant' => 'Cikarang', 'status' => 'vendor_selection',
        ]);
        $sr1_job1 = ServiceRequestJob::create(['service_request_id' => $sr1->id, 'job_code' => $this->svc(), 'job_description' => 'Pembongkaran & Pembuangan Material Lama']);
        ServiceRequestItem::create(['job_id' => $sr1_job1->id, 'item_name' => 'Jasa Bongkar Keramik Lantai', 'quantity' => 1,  'unit' => 'Lot', 'specification' => '±50m2, termasuk angkut buang']);
        ServiceRequestItem::create(['job_id' => $sr1_job1->id, 'item_name' => 'Angkut & Buang Material Sisa','quantity' => 1,  'unit' => 'Lot', 'specification' => 'Biaya dump truck termasuk']);
        $sr1_job2 = ServiceRequestJob::create(['service_request_id' => $sr1->id, 'job_code' => $this->svc(), 'job_description' => 'Pemasangan Interior Baru']);
        $sr1_i3   = ServiceRequestItem::create(['job_id' => $sr1_job2->id, 'item_name' => 'Pemasangan Kitchen Set Modular', 'quantity' => 1,  'unit' => 'Set', 'specification' => 'HPL motif kayu, 4 pintu bawah + gantung']);
        ServiceRequestItem::create(['job_id' => $sr1_job2->id, 'item_name' => 'Pemasangan Keramik 60×60',   'quantity' => 50, 'unit' => 'm2',  'specification' => 'Granit polish, termasuk nat']);
        $rfq_sr1 = Rfq::create(['service_request_id' => $sr1->id, 'rfq_number' => 'RFQ-SR-2026-0001', 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(2), 'status' => 'closed', 'opened_at' => now()->subDays(4), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        $q_sr1_v3 = Quotation::create(['rfq_id' => $rfq_sr1->id, 'vendor_id' => $v3->id, 'total_price' => 10500000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr1_v3= QuotationDetail::create(['quotation_id' => $q_sr1_v3->id, 'service_request_item_id' => $sr1_i3->id, 'offered_price_per_item' => 10500000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr1->id, 'quotation_detail_id' => $qd_sr1_v3->id, 'is_sent_to_user' => true]);
        $q_sr1_v4 = Quotation::create(['rfq_id' => $rfq_sr1->id, 'vendor_id' => $v4->id, 'total_price' => 9100000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr1_v4= QuotationDetail::create(['quotation_id' => $q_sr1_v4->id, 'service_request_item_id' => $sr1_i3->id, 'offered_price_per_item' => 9100000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr1->id, 'quotation_detail_id' => $qd_sr1_v4->id, 'is_sent_to_user' => true]);
        $q_sr1_v5 = Quotation::create(['rfq_id' => $rfq_sr1->id, 'vendor_id' => $v5->id, 'total_price' => 8500000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr1_v5= QuotationDetail::create(['quotation_id' => $q_sr1_v5->id, 'service_request_item_id' => $sr1_i3->id, 'offered_price_per_item' => 8500000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr1->id, 'quotation_detail_id' => $qd_sr1_v5->id, 'is_sent_to_user' => true]);
        // ── SR-02: AWAITING_APPROVAL — Perawatan AC Gedung Utama (2 jobs) ──
        $sr2 = ServiceRequest::create([
            'user_id' => $user->id, 'department' => 'Operations', 'document_number' => 'SR-2026-0002',
            'service_name' => 'Perawatan AC Gedung Utama',
            'submission_date' => now()->subDays(3), 'requested_date' => now()->subDays(2),
            'plant' => 'Cikarang', 'status' => 'submitted',
        ]);
        $sr2_job1 = ServiceRequestJob::create(['service_request_id' => $sr2->id, 'job_code' => $this->svc(), 'job_description' => 'Cleaning & Service Rutin']);
        ServiceRequestItem::create(['job_id' => $sr2_job1->id, 'item_name' => 'Cuci AC Split 1-2 PK',      'quantity' => 20, 'unit' => 'Unit', 'specification' => 'Termasuk freon top-up R32']);
        ServiceRequestItem::create(['job_id' => $sr2_job1->id, 'item_name' => 'Bersih Drain Pan & Pipa',   'quantity' => 20, 'unit' => 'Unit', 'specification' => 'Vacuum drain & disinfect']);
        $sr2_job2 = ServiceRequestJob::create(['service_request_id' => $sr2->id, 'job_code' => $this->svc(), 'job_description' => 'Penggantian Komponen Rusak']);
        ServiceRequestItem::create(['job_id' => $sr2_job2->id, 'item_name' => 'Ganti Kapasitor 35/5 MFD',  'quantity' => 5,  'unit' => 'Pcs',  'specification' => 'Sesuai hasil diagnosa']);
        ServiceRequestItem::create(['job_id' => $sr2_job2->id, 'item_name' => 'Ganti Filter Udara Washable','quantity' => 10, 'unit' => 'Pcs',  'specification' => 'Ukuran sesuai unit masing-masing']);
        // ── SR-03: AWAITING_APPROVAL — Instalasi Jaringan LAN (3 jobs) ──
        $sr3 = ServiceRequest::create([
            'user_id' => $user2->id, 'department' => 'Maintenance', 'document_number' => 'SR-2026-0003',
            'service_name' => 'Instalasi Jaringan LAN Area Produksi',
            'submission_date' => now()->subDays(2), 'requested_date' => now()->subDays(1),
            'plant' => 'Cibitung', 'status' => 'submitted',
        ]);
        $sr3_job1 = ServiceRequestJob::create(['service_request_id' => $sr3->id, 'job_code' => $this->svc(), 'job_description' => 'Instalasi Kabel UTP & Conduit']);
        ServiceRequestItem::create(['job_id' => $sr3_job1->id, 'item_name' => 'Pemasangan Kabel UTP Cat6',    'quantity' => 500, 'unit' => 'Meter', 'specification' => 'Termasuk conduit PVC & klem']);
        ServiceRequestItem::create(['job_id' => $sr3_job1->id, 'item_name' => 'Instalasi Patch Panel 24 Port','quantity' => 2,   'unit' => 'Unit',  'specification' => 'Rack mount 1U']);
        $sr3_job2 = ServiceRequestJob::create(['service_request_id' => $sr3->id, 'job_code' => $this->svc(), 'job_description' => 'Konfigurasi Network Equipment']);
        ServiceRequestItem::create(['job_id' => $sr3_job2->id, 'item_name' => 'Konfigurasi Managed Switch',   'quantity' => 3, 'unit' => 'Unit', 'specification' => 'VLAN, QoS setting']);
        ServiceRequestItem::create(['job_id' => $sr3_job2->id, 'item_name' => 'Konfigurasi Access Point WiFi', 'quantity' => 5, 'unit' => 'Unit', 'specification' => 'SSID, WPA2-Enterprise']);
        $sr3_job3 = ServiceRequestJob::create(['service_request_id' => $sr3->id, 'job_code' => $this->svc(), 'job_description' => 'Testing & Commissioning']);
        ServiceRequestItem::create(['job_id' => $sr3_job3->id, 'item_name' => 'Test Link Speed & Latency',    'quantity' => 1, 'unit' => 'Lot', 'specification' => 'Semua titik node, generate laporan']);
        ServiceRequestItem::create(['job_id' => $sr3_job3->id, 'item_name' => 'Dokumentasi As-Built Drawing', 'quantity' => 1, 'unit' => 'Set', 'specification' => 'PDF + AutoCAD file']);
        // ── SR-04: IN_PROCESS — Perbaikan Atap Warehouse (2 jobs, 3 vendor) ──
        $sr4 = ServiceRequest::create([
            'user_id' => $user2->id, 'department' => 'Maintenance', 'document_number' => 'SR-2026-0004',
            'service_name' => 'Perbaikan Atap Warehouse Gresik',
            'submission_date' => now()->subDays(18), 'requested_date' => now()->subDays(17),
            'plant' => 'Gresik', 'status' => 'vendor_selection',
        ]);
        $sr4_job1 = ServiceRequestJob::create(['service_request_id' => $sr4->id, 'job_code' => $this->svc(), 'job_description' => 'Survei & Persiapan Scaffolding']);
        $sr4_i1   = ServiceRequestItem::create(['job_id' => $sr4_job1->id, 'item_name' => 'Survei Kondisi Atap & RAB',   'quantity' => 1,   'unit' => 'Lot', 'specification' => 'Termasuk foto dokumentasi']);
        ServiceRequestItem::create(['job_id' => $sr4_job1->id, 'item_name' => 'Pasang & Bongkar Scaffolding', 'quantity' => 300, 'unit' => 'm2',  'specification' => 'Tinggi maks 8 meter']);
        $sr4_job2 = ServiceRequestJob::create(['service_request_id' => $sr4->id, 'job_code' => $this->svc(), 'job_description' => 'Penggantian & Perbaikan Atap']);
        ServiceRequestItem::create(['job_id' => $sr4_job2->id, 'item_name' => 'Ganti Spandek Bocor',          'quantity' => 80,  'unit' => 'm2',  'specification' => 'Spandek 0.4mm zinc alum']);
        ServiceRequestItem::create(['job_id' => $sr4_job2->id, 'item_name' => 'Sealant & Waterproofing',      'quantity' => 150, 'unit' => 'ml',  'specification' => 'Sika Flex polyurethane']);
        $rfq_sr4 = Rfq::create(['service_request_id' => $sr4->id, 'rfq_number' => 'RFQ-SR-2026-0004', 'is_sent_to_user' => false, 'status' => 'open', 'opened_at' => now()->subDays(12), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq_sr4->id, 'round' => 1, 'start_date' => now()->subDays(12), 'end_date' => now()->addDays(2), 'status' => 'open']);
        $q_sr4_v4 = Quotation::create(['rfq_id' => $rfq_sr4->id, 'vendor_id' => $v4->id, 'total_price' => 85000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr4_v4= QuotationDetail::create(['quotation_id' => $q_sr4_v4->id, 'service_request_item_id' => $sr4_i1->id, 'offered_price_per_item' => 85000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr4->id, 'quotation_detail_id' => $qd_sr4_v4->id, 'is_sent_to_user' => false]);
        $q_sr4_v6 = Quotation::create(['rfq_id' => $rfq_sr4->id, 'vendor_id' => $v6->id, 'total_price' => 72000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr4_v6= QuotationDetail::create(['quotation_id' => $q_sr4_v6->id, 'service_request_item_id' => $sr4_i1->id, 'offered_price_per_item' => 72000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr4->id, 'quotation_detail_id' => $qd_sr4_v6->id, 'is_sent_to_user' => false]);
        $q_sr4_v7 = Quotation::create(['rfq_id' => $rfq_sr4->id, 'vendor_id' => $v7->id, 'total_price' => 68000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr4_v7= QuotationDetail::create(['quotation_id' => $q_sr4_v7->id, 'service_request_item_id' => $sr4_i1->id, 'offered_price_per_item' => 68000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr4->id, 'quotation_detail_id' => $qd_sr4_v7->id, 'is_sent_to_user' => false]);
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq_sr4->id, 'action' => 'RFQ Created', 'transaction_status' => 'completed', 'notes' => 'RFQ SR-0004 dibuat, menunggu quotation vendor', 'action_date' => now()->subDays(12)]);
        // ── SR-05: IN_PROCESS — Pengecatan Dinding Pabrik (2 jobs, 2 vendor) ──
        $sr5 = ServiceRequest::create([
            'user_id' => $user->id, 'department' => 'Operations', 'document_number' => 'SR-2026-0005',
            'service_name' => 'Pengecatan Dinding Pabrik Area A',
            'submission_date' => now()->subDays(11), 'requested_date' => now()->subDays(10),
            'plant' => 'Cikarang', 'status' => 'vendor_selection',
        ]);
        $sr5_job1 = ServiceRequestJob::create(['service_request_id' => $sr5->id, 'job_code' => $this->svc(), 'job_description' => 'Persiapan Permukaan']);
        $sr5_i1   = ServiceRequestItem::create(['job_id' => $sr5_job1->id, 'item_name' => 'Amplas & Plamur Dinding',           'quantity' => 800, 'unit' => 'm2',  'specification' => 'Sebelum pengecatan']);
        ServiceRequestItem::create(['job_id' => $sr5_job1->id, 'item_name' => 'Pasang Masking Tape & Plastic Cover', 'quantity' => 1,   'unit' => 'Lot', 'specification' => 'Proteksi area sekitar']);
        $sr5_job2 = ServiceRequestJob::create(['service_request_id' => $sr5->id, 'job_code' => $this->svc(), 'job_description' => 'Pengecatan 2 Lapis']);
        ServiceRequestItem::create(['job_id' => $sr5_job2->id, 'item_name' => 'Cat Tembok Eksterior Weathershield', 'quantity' => 800, 'unit' => 'm2',  'specification' => 'Cat Dulux atau setara, abu muda']);
        ServiceRequestItem::create(['job_id' => $sr5_job2->id, 'item_name' => 'Cat Garis Safety Floor Marking',     'quantity' => 200, 'unit' => 'ml',  'specification' => 'Cat epoxy kuning, lebar 10cm']);
        $rfq_sr5 = Rfq::create(['service_request_id' => $sr5->id, 'rfq_number' => 'RFQ-SR-2026-0005', 'is_sent_to_user' => false, 'status' => 'open', 'opened_at' => now()->subDays(7), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq_sr5->id, 'round' => 1, 'start_date' => now()->subDays(7), 'end_date' => now()->addDays(7), 'status' => 'open']);
        $q_sr5_v3 = Quotation::create(['rfq_id' => $rfq_sr5->id, 'vendor_id' => $v3->id, 'total_price' => 42000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr5_v3= QuotationDetail::create(['quotation_id' => $q_sr5_v3->id, 'service_request_item_id' => $sr5_i1->id, 'offered_price_per_item' => 42000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr5->id, 'quotation_detail_id' => $qd_sr5_v3->id, 'is_sent_to_user' => false]);
        $q_sr5_v5 = Quotation::create(['rfq_id' => $rfq_sr5->id, 'vendor_id' => $v5->id, 'total_price' => 36500000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr5_v5= QuotationDetail::create(['quotation_id' => $q_sr5_v5->id, 'service_request_item_id' => $sr5_i1->id, 'offered_price_per_item' => 36500000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr5->id, 'quotation_detail_id' => $qd_sr5_v5->id, 'is_sent_to_user' => false]);
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq_sr5->id, 'action' => 'RFQ Created', 'transaction_status' => 'completed', 'notes' => 'RFQ SR-0005 dibuat untuk SR Pengecatan', 'action_date' => now()->subDays(7)]);
        // ── SR-06: APPROVED — Instalasi CCTV (3 jobs, VS done) ──
        $sr6 = ServiceRequest::create([
            'user_id' => $user2->id, 'department' => 'Maintenance', 'document_number' => 'SR-2026-0006',
            'service_name' => 'Instalasi Sistem CCTV Pabrik',
            'submission_date' => now()->subDays(25), 'requested_date' => now()->subDays(24),
            'plant' => 'Cibitung', 'status' => 'completed',
        ]);
        $sr6_job1 = ServiceRequestJob::create(['service_request_id' => $sr6->id, 'job_code' => $this->svc(), 'job_description' => 'Desain & Survei Lokasi']);
        $sr6_i1   = ServiceRequestItem::create(['job_id' => $sr6_job1->id, 'item_name' => 'Survei Titik CCTV & Layout',    'quantity' => 1,    'unit' => 'Lot',   'specification' => 'Termasuk desain topologi & BOM']);
        $sr6_job2 = ServiceRequestJob::create(['service_request_id' => $sr6->id, 'job_code' => $this->svc(), 'job_description' => 'Pemasangan Hardware']);
        ServiceRequestItem::create(['job_id' => $sr6_job2->id, 'item_name' => 'Pasang CCTV IP 4MP Hikvision', 'quantity' => 24,   'unit' => 'Unit',  'specification' => 'PoE, IR 30m, outdoor vandal-proof']);
        ServiceRequestItem::create(['job_id' => $sr6_job2->id, 'item_name' => 'Instalasi NVR 32CH + HDD 8TB', 'quantity' => 2,    'unit' => 'Unit',  'specification' => 'Rack mount, RAID-1']);
        ServiceRequestItem::create(['job_id' => $sr6_job2->id, 'item_name' => 'Tarik Kabel UTP Cat6 PoE',     'quantity' => 1200, 'unit' => 'Meter', 'specification' => 'Conduit indoor/outdoor']);
        $sr6_job3 = ServiceRequestJob::create(['service_request_id' => $sr6->id, 'job_code' => $this->svc(), 'job_description' => 'Konfigurasi & Training']);
        ServiceRequestItem::create(['job_id' => $sr6_job3->id, 'item_name' => 'Setup Remote Monitoring App',  'quantity' => 1, 'unit' => 'Lot',  'specification' => 'Hik-Connect + email alert']);
        ServiceRequestItem::create(['job_id' => $sr6_job3->id, 'item_name' => 'Training User Operator CCTV', 'quantity' => 1, 'unit' => 'Sesi', 'specification' => '3 jam, max 10 peserta']);
        $rfq_sr6 = Rfq::create(['service_request_id' => $sr6->id, 'rfq_number' => 'RFQ-SR-2026-0006', 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(15), 'status' => 'closed', 'opened_at' => now()->subDays(22), 'closed_at' => now()->subDays(14), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq_sr6->id, 'round' => 1, 'start_date' => now()->subDays(22), 'end_date' => now()->subDays(14), 'status' => 'closed']);
        $q_sr6_v1 = Quotation::create(['rfq_id' => $rfq_sr6->id, 'vendor_id' => $v1->id, 'total_price' => 125000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr6_v1= QuotationDetail::create(['quotation_id' => $q_sr6_v1->id, 'service_request_item_id' => $sr6_i1->id, 'offered_price_per_item' => 125000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        $qs_sr6_v1= QuotationSummary::create(['rfq_id' => $rfq_sr6->id, 'quotation_detail_id' => $qd_sr6_v1->id, 'is_sent_to_user' => true]);
        $q_sr6_v2 = Quotation::create(['rfq_id' => $rfq_sr6->id, 'vendor_id' => $v2->id, 'total_price' => 118000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr6_v2= QuotationDetail::create(['quotation_id' => $q_sr6_v2->id, 'service_request_item_id' => $sr6_i1->id, 'offered_price_per_item' => 118000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        $qs_sr6_v2= QuotationSummary::create(['rfq_id' => $rfq_sr6->id, 'quotation_detail_id' => $qd_sr6_v2->id, 'is_sent_to_user' => true]);
        $sel_sr6  = VendorSelection::create(['rfq_id' => $rfq_sr6->id, 'vendor_id' => $v2->id, 'quotation_id' => $q_sr6_v2->id, 'decision_notes' => 'CV Maju Komputer best price, pengalaman CCTV industri', 'decided_at' => now()->subDays(13)]);
        SelectionItem::create(['vendor_selection_id' => $sel_sr6->id, 'quotation_summary_id' => $qs_sr6_v2->id, 'service_request_item_id' => $sr6_i1->id, 'final_price_per_item' => 118000000, 'final_quantity' => 1, 'notes' => 'Best price dari 2 penawaran']);
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq_sr6->id, 'vendor_selection_id' => $sel_sr6->id, 'action' => 'Vendor Selection Submitted', 'transaction_status' => 'completed', 'notes' => 'CV Maju Komputer dipilih untuk SR Instalasi CCTV', 'action_date' => now()->subDays(13)]);
        // ── SR-07: COMPLETED — Perbaikan Sistem Plumbing (2 jobs) ──
        $sr7 = ServiceRequest::create([
            'user_id' => $user->id, 'department' => 'Operations', 'document_number' => 'SR-2026-0007',
            'service_name' => 'Perbaikan Sistem Plumbing Toilet Lantai 2',
            'submission_date' => now()->subDays(40), 'requested_date' => now()->subDays(39),
            'plant' => 'Gresik', 'status' => 'completed',
        ]);
        $sr7_job1 = ServiceRequestJob::create(['service_request_id' => $sr7->id, 'job_code' => $this->svc(), 'job_description' => 'Inspeksi & Diagnosis Kebocoran']);
        $sr7_i1   = ServiceRequestItem::create(['job_id' => $sr7_job1->id, 'item_name' => 'Camera Inspection Pipa',     'quantity' => 1, 'unit' => 'Lot', 'specification' => 'Push camera pipa 4", laporan video']);
        $sr7_i2   = ServiceRequestItem::create(['job_id' => $sr7_job1->id, 'item_name' => 'Leak Detection Pressure Test', 'quantity' => 1, 'unit' => 'Lot', 'specification' => 'Semua jalur distribusi']);
        $sr7_job2 = ServiceRequestJob::create(['service_request_id' => $sr7->id, 'job_code' => $this->svc(), 'job_description' => 'Perbaikan & Penggantian Pipa']);
        $sr7_i3   = ServiceRequestItem::create(['job_id' => $sr7_job2->id, 'item_name' => 'Ganti Pipa PVC AW 4 inch',       'quantity' => 30, 'unit' => 'Meter', 'specification' => 'Wavin atau setara, termasuk fitting']);
        $sr7_i4   = ServiceRequestItem::create(['job_id' => $sr7_job2->id, 'item_name' => 'Pasang Stop Kran Ball Valve 4"', 'quantity' => 4,  'unit' => 'Pcs',   'specification' => 'Kuningan, full bore']);
        $rfq_sr7 = Rfq::create(['service_request_id' => $sr7->id, 'rfq_number' => 'RFQ-SR-2026-0007', 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(30), 'status' => 'closed', 'opened_at' => now()->subDays(36), 'closed_at' => now()->subDays(28), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq_sr7->id, 'round' => 1, 'start_date' => now()->subDays(36), 'end_date' => now()->subDays(28), 'status' => 'closed']);
        $q_sr7_v3 = Quotation::create(['rfq_id' => $rfq_sr7->id, 'vendor_id' => $v3->id, 'total_price' => 47900000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr7_v3_1= QuotationDetail::create(['quotation_id' => $q_sr7_v3->id, 'service_request_item_id' => $sr7_i1->id, 'offered_price_per_item' => 28000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        $qd_sr7_v3_2= QuotationDetail::create(['quotation_id' => $q_sr7_v3->id, 'service_request_item_id' => $sr7_i2->id, 'offered_price_per_item' => 12000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        $qd_sr7_v3_3= QuotationDetail::create(['quotation_id' => $q_sr7_v3->id, 'service_request_item_id' => $sr7_i3->id, 'offered_price_per_item' => 150000, 'offered_quantity' => 30, 'offered_unit' => 'Pcs']);
        $qd_sr7_v3_4= QuotationDetail::create(['quotation_id' => $q_sr7_v3->id, 'service_request_item_id' => $sr7_i4->id, 'offered_price_per_item' => 850000, 'offered_quantity' => 4, 'offered_unit' => 'Pcs']);

        $qs_sr7_v3_1= QuotationSummary::create(['rfq_id' => $rfq_sr7->id, 'quotation_detail_id' => $qd_sr7_v3_1->id, 'is_sent_to_user' => true]);
        $qs_sr7_v3_2= QuotationSummary::create(['rfq_id' => $rfq_sr7->id, 'quotation_detail_id' => $qd_sr7_v3_2->id, 'is_sent_to_user' => true]);
        $qs_sr7_v3_3= QuotationSummary::create(['rfq_id' => $rfq_sr7->id, 'quotation_detail_id' => $qd_sr7_v3_3->id, 'is_sent_to_user' => true]);
        $qs_sr7_v3_4= QuotationSummary::create(['rfq_id' => $rfq_sr7->id, 'quotation_detail_id' => $qd_sr7_v3_4->id, 'is_sent_to_user' => true]);

        $sel_sr7  = VendorSelection::create(['rfq_id' => $rfq_sr7->id, 'vendor_id' => $v3->id, 'quotation_id' => $q_sr7_v3->id, 'decision_notes' => 'Vendor qualified, harga sesuai budget', 'decided_at' => now()->subDays(26)]);
        SelectionItem::create(['vendor_selection_id' => $sel_sr7->id, 'quotation_summary_id' => $qs_sr7_v3_1->id, 'service_request_item_id' => $sr7_i1->id, 'final_price_per_item' => 28000000, 'final_quantity' => 1, 'notes' => 'Completed on time']);
        SelectionItem::create(['vendor_selection_id' => $sel_sr7->id, 'quotation_summary_id' => $qs_sr7_v3_2->id, 'service_request_item_id' => $sr7_i2->id, 'final_price_per_item' => 12000000, 'final_quantity' => 1, 'notes' => 'Completed on time']);
        SelectionItem::create(['vendor_selection_id' => $sel_sr7->id, 'quotation_summary_id' => $qs_sr7_v3_3->id, 'service_request_item_id' => $sr7_i3->id, 'final_price_per_item' => 150000, 'final_quantity' => 30, 'notes' => 'Completed on time']);
        SelectionItem::create(['vendor_selection_id' => $sel_sr7->id, 'quotation_summary_id' => $qs_sr7_v3_4->id, 'service_request_item_id' => $sr7_i4->id, 'final_price_per_item' => 850000, 'final_quantity' => 4, 'notes' => 'Completed on time']);
        
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq_sr7->id, 'vendor_selection_id' => $sel_sr7->id, 'action' => 'Vendor Selection Submitted', 'transaction_status' => 'completed', 'notes' => 'PT Karya Jasa dipilih untuk SR Plumbing', 'action_date' => now()->subDays(26)]);
        // ── SR-08: AWAITING_APPROVAL — Pekerjaan Sipil Area Produksi (3 jobs) ──
        $sr8 = ServiceRequest::create([
            'user_id' => $user2->id, 'department' => 'Maintenance', 'document_number' => 'SR-2026-0008',
            'service_name' => 'Pekerjaan Sipil Area Produksi Line 3',
            'submission_date' => now()->subDays(1), 'requested_date' => now(),
            'plant' => 'Cikarang', 'status' => 'submitted',
        ]);
        $sr8_job1 = ServiceRequestJob::create(['service_request_id' => $sr8->id, 'job_code' => $this->svc(), 'job_description' => 'Persiapan & Demolisi']);
        ServiceRequestItem::create(['job_id' => $sr8_job1->id, 'item_name' => 'Bongkar Lantai Beton Existing', 'quantity' => 150, 'unit' => 'm2',    'specification' => 'Tebal ±15cm, termasuk disposal']);
        ServiceRequestItem::create(['job_id' => $sr8_job1->id, 'item_name' => 'Galian Saluran Drainase',       'quantity' => 50,  'unit' => 'Meter', 'specification' => 'Kedalaman 50cm, lebar 30cm']);
        $sr8_job2 = ServiceRequestJob::create(['service_request_id' => $sr8->id, 'job_code' => $this->svc(), 'job_description' => 'Pekerjaan Struktur']);
        ServiceRequestItem::create(['job_id' => $sr8_job2->id, 'item_name' => 'Cor Lantai Beton K-300',         'quantity' => 150, 'unit' => 'm2',    'specification' => 'Tebal 15cm, wiremesh M8-200, power trowel']);
        ServiceRequestItem::create(['job_id' => $sr8_job2->id, 'item_name' => 'Pasang U-Ditch Precast 30×30',  'quantity' => 50,  'unit' => 'Meter', 'specification' => 'Cover besi']);
        $sr8_job3 = ServiceRequestJob::create(['service_request_id' => $sr8->id, 'job_code' => $this->svc(), 'job_description' => 'Finishing & Coating']);
        ServiceRequestItem::create(['job_id' => $sr8_job3->id, 'item_name' => 'Cat Epoxy Lantai 2 Lapis',   'quantity' => 150, 'unit' => 'm2',    'specification' => 'Industrial grade, warna abu']);
        ServiceRequestItem::create(['job_id' => $sr8_job3->id, 'item_name' => 'Floor Marking Garis Lajur',   'quantity' => 100, 'unit' => 'Meter', 'specification' => 'Lebar 10cm, cat epoxy kuning']);
        // ── SR-09: IN_PROCESS — Kalibrasi Alat Ukur (2 jobs, 3 vendor) ──
        $sr9 = ServiceRequest::create([
            'user_id' => $user->id, 'department' => 'Operations', 'document_number' => 'SR-2026-0009',
            'service_name' => 'Kalibrasi Alat Ukur Produksi',
            'submission_date' => now()->subDays(7), 'requested_date' => now()->subDays(6),
            'plant' => 'Cibitung', 'status' => 'vendor_selection',
        ]);
        $sr9_job1 = ServiceRequestJob::create(['service_request_id' => $sr9->id, 'job_code' => $this->svc(), 'job_description' => 'Kalibrasi Instrumen Tekanan & Suhu']);
        $sr9_i1   = ServiceRequestItem::create(['job_id' => $sr9_job1->id, 'item_name' => 'Kalibrasi Pressure Gauge',   'quantity' => 15, 'unit' => 'Pcs',  'specification' => 'Range 0-10 bar, akurasi 0.5%']);
        ServiceRequestItem::create(['job_id' => $sr9_job1->id, 'item_name' => 'Kalibrasi Thermocouple Type K', 'quantity' => 10, 'unit' => 'Pcs',  'specification' => 'Range -50~1200°C, NIST traceable']);
        $sr9_job2 = ServiceRequestJob::create(['service_request_id' => $sr9->id, 'job_code' => $this->svc(), 'job_description' => 'Kalibrasi Alat Ukur Dimensi & Massa']);
        ServiceRequestItem::create(['job_id' => $sr9_job2->id, 'item_name' => 'Kalibrasi Vernier Caliper',     'quantity' => 8,  'unit' => 'Pcs',  'specification' => '0-300mm, resolusi 0.02mm']);
        ServiceRequestItem::create(['job_id' => $sr9_job2->id, 'item_name' => 'Kalibrasi Timbangan Digital',   'quantity' => 5,  'unit' => 'Unit', 'specification' => 'Kapasitas 200kg, ketelitian 0.1kg']);
        $rfq_sr9 = Rfq::create(['service_request_id' => $sr9->id, 'rfq_number' => 'RFQ-SR-2026-0009', 'is_sent_to_user' => false, 'status' => 'open', 'opened_at' => now()->subDays(4), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq_sr9->id, 'round' => 1, 'start_date' => now()->subDays(4), 'end_date' => now()->addDays(10), 'status' => 'open']);
        $q_sr9_v5 = Quotation::create(['rfq_id' => $rfq_sr9->id, 'vendor_id' => $v5->id, 'total_price' => 12500000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr9_v5= QuotationDetail::create(['quotation_id' => $q_sr9_v5->id, 'service_request_item_id' => $sr9_i1->id, 'offered_price_per_item' => 12500000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr9->id, 'quotation_detail_id' => $qd_sr9_v5->id, 'is_sent_to_user' => false]);
        $q_sr9_v6 = Quotation::create(['rfq_id' => $rfq_sr9->id, 'vendor_id' => $v6->id, 'total_price' => 10800000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr9_v6= QuotationDetail::create(['quotation_id' => $q_sr9_v6->id, 'service_request_item_id' => $sr9_i1->id, 'offered_price_per_item' => 10800000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr9->id, 'quotation_detail_id' => $qd_sr9_v6->id, 'is_sent_to_user' => false]);
        $q_sr9_v7 = Quotation::create(['rfq_id' => $rfq_sr9->id, 'vendor_id' => $v7->id, 'total_price' => 11200000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr9_v7= QuotationDetail::create(['quotation_id' => $q_sr9_v7->id, 'service_request_item_id' => $sr9_i1->id, 'offered_price_per_item' => 11200000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr9->id, 'quotation_detail_id' => $qd_sr9_v7->id, 'is_sent_to_user' => false]);
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq_sr9->id, 'action' => 'RFQ Created', 'transaction_status' => 'completed', 'notes' => 'RFQ SR-0009 dibuat untuk Kalibrasi Alat Ukur', 'action_date' => now()->subDays(4)]);
        // ── SR-10: AWAITING_APPROVAL — Pembersihan Tangki Chemical (2 jobs) ──
        $sr10 = ServiceRequest::create([
            'user_id' => $user2->id, 'department' => 'Maintenance', 'document_number' => 'SR-2026-0010',
            'service_name' => 'Pembersihan Tangki Storage Chemical',
            'submission_date' => now()->subDays(1), 'requested_date' => now(),
            'plant' => 'Gresik', 'status' => 'submitted',
        ]);
        $sr10_job1= ServiceRequestJob::create(['service_request_id' => $sr10->id, 'job_code' => $this->svc(), 'job_description' => 'Degassing & Purging Tangki']);
        ServiceRequestItem::create(['job_id' => $sr10_job1->id, 'item_name' => 'Degassing Tangki 50m3',          'quantity' => 2, 'unit' => 'Unit', 'specification' => 'Nitrogen purging, GAS detector clearance']);
        ServiceRequestItem::create(['job_id' => $sr10_job1->id, 'item_name' => 'Confined Space Permit & Safety', 'quantity' => 1, 'unit' => 'Lot',  'specification' => 'Sertifikat K3 confined space']);
        $sr10_job2= ServiceRequestJob::create(['service_request_id' => $sr10->id, 'job_code' => $this->svc(), 'job_description' => 'Cleaning & Inspeksi Internal']);
        ServiceRequestItem::create(['job_id' => $sr10_job2->id, 'item_name' => 'Chemical Cleaning Tangki',       'quantity' => 2, 'unit' => 'Unit', 'specification' => 'High pressure water jet + neutralizer']);
        ServiceRequestItem::create(['job_id' => $sr10_job2->id, 'item_name' => 'Visual Inspection & UT Test',   'quantity' => 2, 'unit' => 'Unit', 'specification' => 'Thickness gauging, foto dokumentasi, laporan']);
        // ── SR-11: IN_PROCESS — Retrofit Panel Listrik MCC (3 jobs, 4 vendor, komparasi terbaik) ──
        $sr11 = ServiceRequest::create([
            'user_id' => $user->id, 'department' => 'Operations', 'document_number' => 'SR-2026-0011',
            'service_name' => 'Retrofit Panel Listrik MCC Lini 2',
            'submission_date' => now()->subDays(14), 'requested_date' => now()->subDays(13),
            'plant' => 'Cikarang', 'status' => 'vendor_selection',
        ]);
        $sr11_job1= ServiceRequestJob::create(['service_request_id' => $sr11->id, 'job_code' => $this->svc(), 'job_description' => 'Studi & Detail Engineering Design']);
        $sr11_i1  = ServiceRequestItem::create(['job_id' => $sr11_job1->id, 'item_name' => 'Load Flow & Short Circuit Study',   'quantity' => 1, 'unit' => 'Lot', 'specification' => 'Software ETAP, laporan & sertifikasi']);
        ServiceRequestItem::create(['job_id' => $sr11_job1->id, 'item_name' => 'Gambar Detail Engineering Panel MCC', 'quantity' => 1, 'unit' => 'Set', 'specification' => 'SLD + Layout + Wiring diagram AutoCAD']);
        $sr11_job2= ServiceRequestJob::create(['service_request_id' => $sr11->id, 'job_code' => $this->svc(), 'job_description' => 'Pengadaan & Pemasangan Komponen']);
        ServiceRequestItem::create(['job_id' => $sr11_job2->id, 'item_name' => 'Pasang MCCB 3P 400A Schneider',  'quantity' => 4,  'unit' => 'Unit',  'specification' => 'Icu 50kA, komplet dengan trafo CT']);
        ServiceRequestItem::create(['job_id' => $sr11_job2->id, 'item_name' => 'Pasang Soft Starter 75kW ABB',   'quantity' => 2,  'unit' => 'Unit',  'specification' => 'PSR/PSTX series, bypass contactor']);
        ServiceRequestItem::create(['job_id' => $sr11_job2->id, 'item_name' => 'Rewiring Bus Bar Copper 100×10', 'quantity' => 20, 'unit' => 'Meter', 'specification' => 'TPN, sleeve heat shrink']);
        $sr11_job3= ServiceRequestJob::create(['service_request_id' => $sr11->id, 'job_code' => $this->svc(), 'job_description' => 'Testing, Commissioning & Training']);
        ServiceRequestItem::create(['job_id' => $sr11_job3->id, 'item_name' => 'Testing Insulation & Relay',      'quantity' => 1, 'unit' => 'Lot', 'specification' => 'Megger test, OC & GF relay setting']);
        ServiceRequestItem::create(['job_id' => $sr11_job3->id, 'item_name' => 'Commissioning & Training Operator','quantity' => 1, 'unit' => 'Lot', 'specification' => '1 hari komisioning + 0.5 hari training']);
        $rfq_sr11 = Rfq::create(['service_request_id' => $sr11->id, 'rfq_number' => 'RFQ-SR-2026-0011', 'is_sent_to_user' => false, 'status' => 'open', 'opened_at' => now()->subDays(9), 'vendor_token' => \Illuminate\Support\Str::random(32), 'token_expires_at' => now()->addDays(7)]);
        QuotationPeriod::create(['rfq_id' => $rfq_sr11->id, 'round' => 1, 'start_date' => now()->subDays(9), 'end_date' => now()->addDays(5), 'status' => 'open']);
        // 4 vendor — komparasi harga terbaik
        $q_sr11_v1 = Quotation::create(['rfq_id' => $rfq_sr11->id, 'vendor_id' => $v1->id, 'total_price' => 380000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr11_v1= QuotationDetail::create(['quotation_id' => $q_sr11_v1->id, 'service_request_item_id' => $sr11_i1->id, 'offered_price_per_item' => 380000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr11->id, 'quotation_detail_id' => $qd_sr11_v1->id, 'is_sent_to_user' => false]);
        $q_sr11_v4 = Quotation::create(['rfq_id' => $rfq_sr11->id, 'vendor_id' => $v4->id, 'total_price' => 345000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr11_v4= QuotationDetail::create(['quotation_id' => $q_sr11_v4->id, 'service_request_item_id' => $sr11_i1->id, 'offered_price_per_item' => 345000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr11->id, 'quotation_detail_id' => $qd_sr11_v4->id, 'is_sent_to_user' => false]);
        $q_sr11_v5 = Quotation::create(['rfq_id' => $rfq_sr11->id, 'vendor_id' => $v5->id, 'total_price' => 298000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']); // CHEAPEST
        $qd_sr11_v5= QuotationDetail::create(['quotation_id' => $q_sr11_v5->id, 'service_request_item_id' => $sr11_i1->id, 'offered_price_per_item' => 298000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr11->id, 'quotation_detail_id' => $qd_sr11_v5->id, 'is_sent_to_user' => false]);
        $q_sr11_v7 = Quotation::create(['rfq_id' => $rfq_sr11->id, 'vendor_id' => $v7->id, 'total_price' => 315000000, 'status' => 'finalized', 'note' => 'Penawaran standar dari vendor']);
        $qd_sr11_v7= QuotationDetail::create(['quotation_id' => $q_sr11_v7->id, 'service_request_item_id' => $sr11_i1->id, 'offered_price_per_item' => 315000000, 'offered_quantity' => 1, 'offered_unit' => 'Pcs']);
        QuotationSummary::create(['rfq_id' => $rfq_sr11->id, 'quotation_detail_id' => $qd_sr11_v7->id, 'is_sent_to_user' => false]);
        History::create(['user_id' => $admin->id, 'rfq_id' => $rfq_sr11->id, 'action' => 'RFQ Created', 'transaction_status' => 'completed', 'notes' => 'RFQ SR-0011 dibuat untuk Retrofit Panel MCC Lini 2', 'action_date' => now()->subDays(9)]);
        // ── History umum ──────────────────────────────────────────────────────
        History::create(['user_id' => $user->id,  'action' => 'User Account Created', 'notes' => 'Akun John Requester dibuat.',  'action_date' => now()->subDays(60)]);
        History::create(['user_id' => $user2->id, 'action' => 'User Account Created', 'notes' => 'Akun Budi Santoso dibuat.',    'action_date' => now()->subDays(45)]);

        // ── Fix Dummy Data: Auto-generate full items Quotation Details for Service Requests ──
        foreach (Rfq::whereNotNull('service_request_id')->get() as $rfq) {
            $srItems = ServiceRequestItem::whereHas('job', function($q) use ($rfq) {
                $q->where('service_request_id', $rfq->service_request_id);
            })->get();
            $itemCount = $srItems->count();
            if ($itemCount == 0) continue;
            
            foreach (Quotation::where('rfq_id', $rfq->id)->get() as $quotation) {
                // Bagi harga total secara merata ke seluruh item agar subtotal valid
                $avgPrice = (int)($quotation->total_price / $itemCount);
                
                // Hapus detail lama yang tidak lengkap
                $existingQdIds = QuotationDetail::where('quotation_id', $quotation->id)->pluck('id');
                $existingQsIds = QuotationSummary::whereIn('quotation_detail_id', $existingQdIds)->pluck('id');
                
                // Backup SelectionItems yang terhubung dengan QuotationSummary ini
                $backedUpSelections = SelectionItem::whereIn('quotation_summary_id', $existingQsIds)->get()->toArray();
                
                QuotationSummary::whereIn('quotation_detail_id', $existingQdIds)->delete();
                QuotationDetail::where('quotation_id', $quotation->id)->delete();
                
                $newQsIds = [];
                // Buat ulang penawaran (quotation) untuk semua item di dalam SR tersebut
                foreach ($srItems as $item) {
                    $qd = QuotationDetail::create([
                        'quotation_id' => $quotation->id,
                        'service_request_item_id' => $item->id,
                        'offered_price_per_item' => $avgPrice,
                        'offered_quantity' => $item->quantity
                    ]);
                    $qs = QuotationSummary::create([
                        'rfq_id' => $rfq->id,
                        'quotation_detail_id' => $qd->id,
                        'is_sent_to_user' => $rfq->is_sent_to_user
                    ]);
                    $newQsIds[$item->id] = $qs->id;
                }
                
                // Restore SelectionItems
                foreach ($backedUpSelections as $backup) {
                    $itemId = $backup['service_request_item_id'];
                    if ($itemId && isset($newQsIds[$itemId])) {
                        SelectionItem::create([
                            'vendor_selection_id' => $backup['vendor_selection_id'],
                            'quotation_summary_id' => $newQsIds[$itemId],
                            'service_request_item_id' => $itemId,
                            'final_price_per_item' => $backup['final_price_per_item'],
                            'final_quantity' => $backup['final_quantity'],
                            'notes' => $backup['notes'],
                        ]);
                    }
                }
            }
        }
    }
}