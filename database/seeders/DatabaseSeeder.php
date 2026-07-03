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
use Carbon\Carbon;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    private int $itmSeq = 0;
    private int $svcSeq = 0;

    private function itm(): string { return 'ITM-' . str_pad(++$this->itmSeq, 4, '0', STR_PAD_LEFT); }
    private function svc(): string { return 'SVC-' . str_pad(++$this->svcSeq, 4, '0', STR_PAD_LEFT); }

    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // ── Wipe all tables ───────────────────────────────────────────────────
        $tables = [
            'items','history','selection_items','vendor_selections',
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

        $users = [];
        $users[] = User::create(['name' => 'John Requester', 'email' => 'user@company.com', 'password' => Hash::make('password'), 'role' => 'user', 'department' => 'Operations']);
        $users[] = User::create(['name' => 'Budi Santoso', 'email' => 'budi@company.com', 'password' => Hash::make('password'), 'role' => 'user', 'department' => 'Maintenance']);
        $users[] = User::create(['name' => 'Siti Aminah', 'email' => 'siti@company.com', 'password' => Hash::make('password'), 'role' => 'user', 'department' => 'Quality Control']);

        // ════════════════════════════════════════════════════════════════════
        // 2. VENDORS
        // ════════════════════════════════════════════════════════════════════
        $vendors = [];
        for ($i = 0; $i < 15; $i++) {
            $vendors[] = Vendor::create([
                'vendor_name' => $faker->company,
                'location' => $faker->city,
                'email' => $faker->unique()->companyEmail,
                'status' => 'active'
            ]);
        }

        // ════════════════════════════════════════════════════════════════════
        // 3. PR/SR GENERATOR (100 Records)
        $goodsItems = [
            // IT & Electronics
            ['name' => 'Laptop Thinkpad E15', 'spec' => 'Core i7, 16GB RAM', 'unit' => 'Unit', 'price' => 15000000],
            ['name' => 'Monitor Dell 24 inch', 'spec' => 'FHD IPS 60Hz', 'unit' => 'Pcs', 'price' => 2000000],
            ['name' => 'Kabel UTP Cat6', 'spec' => 'Belden 1 Roll 305m', 'unit' => 'Roll', 'price' => 1800000],
            ['name' => 'Mouse Wireless Logitech', 'spec' => 'M220 Silent', 'unit' => 'Pcs', 'price' => 120000],
            ['name' => 'Keyboard Mechanical', 'spec' => 'Keychron K2', 'unit' => 'Pcs', 'price' => 1500000],
            ['name' => 'UPS APC 1000VA', 'spec' => 'Back-UPS Pro', 'unit' => 'Unit', 'price' => 2500000],
            ['name' => 'SSD Samsung 1TB', 'spec' => 'NVMe M.2 980 Pro', 'unit' => 'Pcs', 'price' => 2200000],
            ['name' => 'Router Mikrotik', 'spec' => 'RB450Gx4', 'unit' => 'Unit', 'price' => 1850000],
            ['name' => 'Switch Hub Cisco', 'spec' => '24 Port Gigabit', 'unit' => 'Unit', 'price' => 4500000],
            ['name' => 'Webcam HD 1080p', 'spec' => 'Logitech C920', 'unit' => 'Pcs', 'price' => 1100000],
            
            // Office Supplies
            ['name' => 'Tinta Printer Epson', 'spec' => '003 Black', 'unit' => 'Botol', 'price' => 85000],
            ['name' => 'Kertas HVS A4 80gsm', 'spec' => 'PaperOne 1 Dus', 'unit' => 'Dus', 'price' => 250000],
            ['name' => 'Meja Kerja Kantor', 'spec' => '120x60cm Kayu Jati', 'unit' => 'Unit', 'price' => 1200000],
            ['name' => 'Kursi Ergonomis', 'spec' => 'IKEA Markus', 'unit' => 'Unit', 'price' => 2500000],
            ['name' => 'Papan Tulis Whiteboard', 'spec' => '120x240cm Magnetik', 'unit' => 'Pcs', 'price' => 850000],
            ['name' => 'Proyektor Epson', 'spec' => 'EB-X06 XGA', 'unit' => 'Unit', 'price' => 5600000],
            ['name' => 'Spidol Snowman', 'spec' => 'Board Marker Hitam', 'unit' => 'Lusin', 'price' => 85000],
            ['name' => 'Map Folder Plastik', 'spec' => 'Isi 50 Pcs', 'unit' => 'Pak', 'price' => 150000],
            
            // Industrial & Safety
            ['name' => 'Asam Sulfat 98%', 'spec' => 'Grade Industri, sertifikasi COA', 'unit' => 'Liter', 'price' => 50000],
            ['name' => 'Helm Safety Full Face', 'spec' => 'ANSI Z89.1, warna putih', 'unit' => 'Pcs', 'price' => 150000],
            ['name' => 'Sepatu Safety', 'spec' => 'Krusher Ukuran 42', 'unit' => 'Pasang', 'price' => 450000],
            ['name' => 'Sarung Tangan Las', 'spec' => 'Kulit Sapi Asli', 'unit' => 'Pasang', 'price' => 85000],
            ['name' => 'Masker N95 3M', 'spec' => 'Particulate Respirator 8210', 'unit' => 'Box', 'price' => 250000],
            ['name' => 'Kacamata Safety', 'spec' => 'Anti-Scratch Clear', 'unit' => 'Pcs', 'price' => 45000],
            ['name' => 'Ear Plug 3M', 'spec' => 'Corded Silicone', 'unit' => 'Box', 'price' => 150000],
            ['name' => 'Rompi Safety Reflektif', 'spec' => 'Hijau Stabilo 2 Garis', 'unit' => 'Pcs', 'price' => 35000],
            ['name' => 'Fire Extinguisher', 'spec' => 'APAR Dry Chemical 3kg', 'unit' => 'Tabung', 'price' => 450000],
            ['name' => 'Oli Mesin Pertamina', 'spec' => 'Meditran SX 15W-40', 'unit' => 'Drum', 'price' => 6500000],
            
            // Miscellaneous
            ['name' => 'Lampu LED Philips', 'spec' => '14 Watt Cool Daylight', 'unit' => 'Pcs', 'price' => 45000],
            ['name' => 'Sapu Ijuk', 'spec' => 'Gagang Kayu', 'unit' => 'Pcs', 'price' => 25000],
            ['name' => 'Kain Pel Lantai', 'spec' => 'Microfiber', 'unit' => 'Pcs', 'price' => 65000],
            ['name' => 'Sabun Cuci Tangan', 'spec' => 'Lifebuoy 5 Liter', 'unit' => 'Jerigen', 'price' => 180000],
        ];

        // ════════════════════════════════════════════════════════════════════
        // 2.5. MASTER ITEMS
        // ════════════════════════════════════════════════════════════════════
        foreach ($goodsItems as $idx => $itemDef) {
            $fixedId = 'ITM-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
            \App\Models\Item::create([
                'item_code' => $fixedId,
                'item_name' => $itemDef['name'],
                'unit' => $itemDef['unit'],
                'specification' => $itemDef['spec'],
                'item_notes' => null,
                'is_archived' => false,
            ]);
        }

        $serviceJobs = [
            'Kalibrasi Timbangan Digital', 'Service AC Split 1PK', 'Pembersihan Tandon Air', 
            'Perbaikan Mesin CNC', 'Instalasi Jaringan LAN', 'Sertifikasi ISO 9001', 'Perbaikan Atap Pabrik',
            'Maintenance Genset 500kVA', 'Perbaikan Pipa Hidrolik', 'Kalibrasi Pressure Gauge',
            'Pembersihan Saluran Pipa HVAC', 'Instalasi CCTV 8 Titik', 'Perbaikan Pintu Otomatis',
            'Audit K3 Lingkungan', 'Pelatihan First Aid Karyawan', 'Perbaikan Pompa Air Submersible',
            'Sertifikasi Kelayakan Lift Barang', 'Inspeksi Instalasi Listrik', 'Perawatan Panel Surya',
            'Pengecatan Ulang Dinding Gudang', 'Pengendalian Hama (Pest Control)', 'Sewa Scaffolding Bulanan'
        ];

        $statuses = ['submitted', 'vendor_search', 'vendor_selection', 'completed'];

        for ($i = 0; $i < 100; $i++) {
            $isPr = $faker->boolean(60); // 60% PR, 40% SR
            $status = $faker->randomElement($statuses);
            
            // Generate logical dates
            $submissionDate = Carbon::instance($faker->dateTimeBetween('-2 years', '-1 month'));
            $requestedDate = (clone $submissionDate)->addDays($faker->numberBetween(0, 2));
            $needDate = (clone $requestedDate)->addDays($faker->numberBetween(10, 30));

            $user = $faker->randomElement($users);
            
            if ($isPr) {
                // CREATE PR
                $prYearCount = PurchaseRequest::whereYear('created_at', $submissionDate->year)->count() + 1;
                $docNum = 'PR-' . $submissionDate->format('Y') . '-' . str_pad($prYearCount, 4, '0', STR_PAD_LEFT);

                $pr = PurchaseRequest::create([
                    'user_id' => $user->id,
                    'document_number' => $docNum,
                    'title' => 'Pengadaan ' . $faker->words(3, true),
                    'department' => $user->department,
                    'priority' => $faker->randomElement(['low', 'normal', 'high']),
                    'plant' => $faker->randomElement(['Cikarang', 'Cibitung', 'Gresik']),
                    'submission_date' => $submissionDate,
                    'requested_date' => $requestedDate,
                    'need_date' => $needDate,
                    'note' => $faker->sentence,
                    'status' => $status,
                    'created_at' => $submissionDate,
                    'updated_at' => $submissionDate,
                ]);

                $numItems = $faker->numberBetween(1, 4);
                $items = [];
                for ($j = 0; $j < $numItems; $j++) {
                    $itemKey = array_rand($goodsItems);
                    $itemDef = $goodsItems[$itemKey];
                    $fixedId = 'ITM-' . str_pad($itemKey + 1, 4, '0', STR_PAD_LEFT);
                    $qty = $faker->numberBetween(1, 20);
                    $items[] = PurchaseRequestItem::create([
                        'purchase_request_id' => $pr->id,
                        'item_id' => $fixedId,
                        'item_name' => $itemDef['name'],
                        'quantity' => $qty,
                        'unit' => $itemDef['unit'],
                        'specification' => $itemDef['spec'],
                    ]);
                }

                $this->processProcurementWorkflow($pr, null, $items, $status, $submissionDate, $admin, $users, $vendors, $faker, $goodsItems);

            } else {
                // CREATE SR
                $srYearCount = ServiceRequest::whereYear('created_at', $submissionDate->year)->count() + 1;
                $docNum = 'SR-' . $submissionDate->format('Y') . '-' . str_pad($srYearCount, 4, '0', STR_PAD_LEFT);

                $sr = ServiceRequest::create([
                    'user_id' => $user->id,
                    'department' => $user->department,
                    'document_number' => $docNum,
                    'service_name' => $faker->randomElement(['Proyek Maintenance Tahunan', 'Proyek Perbaikan Rutin', 'Proyek Instalasi Baru', 'Proyek Audit Sistem', 'Proyek Upgrade Fasilitas', 'Proyek Relokasi Pabrik', 'Proyek Perluasan Gudang']),
                    'submission_date' => $submissionDate,
                    'requested_date' => $requestedDate,
                    'plant' => $faker->randomElement(['Cikarang', 'Cibitung', 'Gresik']),
                    'status' => $status,
                    'created_at' => $submissionDate,
                    'updated_at' => $submissionDate,
                ]);

                $numJobs = $faker->numberBetween(1, 2);
                $items = [];
                for ($j = 0; $j < $numJobs; $j++) {
                    $job = ServiceRequestJob::create([
                        'service_request_id' => $sr->id,
                        'job_description' => $faker->randomElement($serviceJobs),
                    ]);

                    $numItems = $faker->numberBetween(1, 3);
                    for ($k = 0; $k < $numItems; $k++) {
                        $itemKey = array_rand($goodsItems);
                        $itemDef = $goodsItems[$itemKey];
                        $fixedId = 'ITM-' . str_pad($itemKey + 1, 4, '0', STR_PAD_LEFT);
                        $qty = $faker->numberBetween(1, 10);
                        $items[] = ServiceRequestItem::create([
                            'job_id' => $job->id,
                            'item_id' => $fixedId,
                            'item_name' => $itemDef['name'],
                            'quantity' => $qty,
                            'unit' => $itemDef['unit'],
                            'specification' => $itemDef['spec'],
                        ]);
                    }
                }

                $this->processProcurementWorkflow(null, $sr, $items, $status, $submissionDate, $admin, $users, $vendors, $faker, $goodsItems);
            }
        }
    }

    private function processProcurementWorkflow($pr, $sr, $items, $status, $submissionDate, $admin, $users, $vendors, $faker, $goodsItems)
    {
        if (in_array($status, ['vendor_search', 'vendor_selection', 'completed'])) {
            $rfqOpenDate = (clone $submissionDate)->addDays($faker->numberBetween(1, 3));
            $isClosed = in_array($status, ['vendor_selection', 'completed']);
            $rfqCloseDate = $isClosed ? (clone $rfqOpenDate)->addDays($faker->numberBetween(3, 7)) : null;

            $todayCount = Rfq::whereDate('created_at', clone $rfqOpenDate)->count() + 1;
            $rfq = Rfq::create([
                'purchase_request_id' => $pr ? $pr->id : null,
                'service_request_id' => $sr ? $sr->id : null,
                'rfq_number' => 'RFQ-' . $rfqOpenDate->format('Y-md') . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT),
                'status' => $isClosed ? 'closed' : 'open',
                'opened_at' => $rfqOpenDate,
                'closed_at' => $rfqCloseDate,
                'vendor_token' => \Illuminate\Support\Str::random(32),
                'token_expires_at' => (clone $rfqOpenDate)->addDays(14),
                'is_sent_to_user' => $status == 'vendor_selection' ? $faker->boolean(80) : ($status == 'completed' ? true : false),
                'sent_to_user_at' => $isClosed ? (clone $rfqCloseDate)->addDays(1) : null,
                'created_at' => $rfqOpenDate,
                'updated_at' => $rfqOpenDate,
            ]);

            QuotationPeriod::create([
                'rfq_id' => $rfq->id,
                'round' => 1,
                'start_date' => $rfqOpenDate,
                'end_date' => $isClosed ? $rfqCloseDate : (clone $rfqOpenDate)->addDays(7),
                'status' => $isClosed ? 'closed' : 'open',
            ]);

            History::create([
                'user_id' => $admin->id,
                'rfq_id' => $rfq->id,
                'action' => 'RFQ Created',
                'transaction_status' => 'completed',
                'notes' => 'RFQ opened for sourcing',
                'action_date' => $rfqOpenDate,
                'created_at' => $rfqOpenDate,
            ]);

            $numVendors = $faker->numberBetween(3, 5);
            $selectedVendors = $faker->randomElements($vendors, $numVendors);
            
            $quotations = [];
            foreach ($selectedVendors as $v) {
                $qDate = (clone $rfqOpenDate)->addDays($faker->numberBetween(1, $isClosed ? 2 : 5));
                if ($qDate > $rfqCloseDate && $isClosed) $qDate = clone $rfqCloseDate;

                VendorQuotation::create([
                    'rfq_id' => $rfq->id,
                    'vendor_id' => $v->id,
                    'status' => 'submitted',
                    'submitted_at' => $qDate,
                ]);

                $quotation = Quotation::create([
                    'rfq_id' => $rfq->id,
                    'vendor_id' => $v->id,
                    'total_price' => 0,
                    'status' => $isClosed ? 'finalized' : 'submitted',
                    'note' => $faker->sentence,
                    'created_at' => $qDate,
                    'updated_at' => $qDate,
                ]);

                $quotations[] = $quotation;

                $totalPrice = 0;
                foreach ($items as $item) {
                    $basePrice = 100000;
                    foreach ($goodsItems as $gi) {
                        if ($gi['name'] == $item->item_name) {
                            $basePrice = $gi['price'];
                            break;
                        }
                    }

                    $offeredPrice = $basePrice * $faker->randomFloat(2, 0.9, 1.2);
                    $offeredQty = $item->quantity;
                    if ($faker->boolean(10)) {
                        $offeredQty = max(1, $item->quantity - 1);
                    }

                    $qd = QuotationDetail::create([
                        'quotation_id' => $quotation->id,
                        'purchase_request_item_id' => $pr ? $item->id : null,
                        'service_request_item_id' => $sr ? $item->id : null,
                        'offered_price_per_item' => $offeredPrice,
                        'offered_quantity' => $offeredQty,
                        'offered_unit' => $item->unit,
                    ]);

                    $totalPrice += ($offeredPrice * $offeredQty);

                    if ($isClosed) {
                        QuotationSummary::create([
                            'rfq_id' => $rfq->id,
                            'quotation_detail_id' => $qd->id,
                            'is_sent_to_user' => true,
                            'sent_to_user_at' => (clone $rfqCloseDate)->addDays(1),
                        ]);
                    }
                }
                
                $quotation->update(['total_price' => $totalPrice]);
            }

            if ($status == 'completed' && count($quotations) > 0) {
                $selDate = (clone $rfqCloseDate)->addDays($faker->numberBetween(1, 3));
                
                $bestQuote = $faker->randomElement($quotations);
                
                $sel = VendorSelection::create([
                    'rfq_id' => $rfq->id,
                    'vendor_id' => $bestQuote->vendor_id,
                    'quotation_id' => $bestQuote->id,
                    'decision_notes' => 'Selected by user based on specs and pricing.',
                    'decided_at' => $selDate,
                    'created_at' => $selDate,
                    'updated_at' => $selDate,
                ]);

                foreach ($bestQuote->details()->get() as $qd) {
                    $qsId = null;
                    $qs = QuotationSummary::where('quotation_detail_id', $qd->id)->first();
                    if ($qs) $qsId = $qs->id;

                    SelectionItem::create([
                        'vendor_selection_id' => $sel->id,
                        'quotation_summary_id' => $qsId,
                        'purchase_request_item_id' => $pr ? $qd->purchase_request_item_id : null,
                        'service_request_item_id' => $sr ? $qd->service_request_item_id : null,
                        'final_price_per_item' => $qd->offered_price_per_item,
                        'final_quantity' => $qd->offered_quantity,
                        'notes' => null,
                    ]);
                }

                $userToSelect = $pr ? $pr->user_id : $sr->user_id;

                History::create([
                    'user_id' => $userToSelect,
                    'vendor_id' => $bestQuote->vendor_id,
                    'rfq_id' => $rfq->id,
                    'vendor_selection_id' => $sel->id,
                    'action' => 'Vendor Selection Submitted',
                    'transaction_status' => 'completed',
                    'notes' => 'Selection finalized and PO created.',
                    'action_date' => $selDate,
                    'created_at' => $selDate,
                ]);
            }
        }
    }
}