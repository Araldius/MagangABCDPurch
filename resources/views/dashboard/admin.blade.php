@extends('layouts.app')
@php
    $pageTitle = 'Procurement Admin Dashboard';
@endphp

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dash-container { display: flex; flex-direction: column; gap: 20px; }
    
    /* Header & Filters */
    .dash-header { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 16px 24px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); flex-wrap: wrap; gap: 16px; }
    .dash-title h1 { font-size: 20px; font-weight: 700; color: #1e3a8a; margin:0; }
    .dash-title p { font-size: 13px; color: #6b7280; margin: 4px 0 0; }
    .dash-filters { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .filter-select, .filter-input { padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 13px; outline: none; background: #f9fafb; color: #374151; font-weight: 500; }
    .btn-primary { background: #3b82f6; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; }
    .btn-primary:hover { background: #2563eb; }
    .btn-outline { background: #fff; color: #374151; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; border: 1px solid #d1d5db; cursor: pointer; }
    .btn-outline:hover { background: #f3f4f6; }
    
    /* Charts Grid */
    .chart-row-2 { display: flex; flex-wrap: wrap; gap: 16px; width: 100%; margin-bottom: 16px; }
    .chart-row-3 { display: flex; flex-wrap: wrap; gap: 16px; width: 100%; margin-bottom: 16px; }
    .chart-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid #f3f4f6; display: flex; flex-direction: column; width: calc(50% - 8px); box-sizing: border-box; position: relative; }
    .chart-header { font-size: 15px; font-weight: 600; color: #1e3a8a; margin-bottom: 16px; }
    
    /* Modal Base */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
    .modal-content { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); display: flex; flex-direction: column; max-height: 90vh; }
    .modal-overlay.active { display: flex; }
    .modal-header { padding: 16px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .modal-title { font-weight: 700; font-size: 16px; color: #1e3a8a; }
    .modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #6b7280; }
    .modal-body { padding: 24px; overflow-y: auto; }
    
    /* Table inside Modal */
    .data-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .data-table th { background: #f8fafc; padding: 12px; text-align: left; font-size: 12px; color: #475569; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; cursor: pointer; user-select: none; }
    .data-table th:hover { background: #f1f5f9; }
    .data-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #334155; }
    .pagination { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #64748b; }
    .page-controls { display: flex; gap: 8px; }
    .page-btn { padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff; cursor: pointer; color: #334155; }
    .page-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .page-btn:hover:not(:disabled) { background: #f1f5f9; }

    /* Checkbox list */
    .checkbox-list { max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px; }
    .checkbox-item { display: flex; align-items: center; gap: 8px; padding: 6px; }
</style>

<div class="dash-container">
    
    <!-- Top Bar & Filters -->
    <div class="dash-header">
        <div class="dash-title">
            <h1>Procurement Dashboard</h1>
            <p>Full Visual Analytics & Drill-down</p>
        </div>
        <div class="dash-filters">
            <select id="time_mode" class="filter-select" onchange="toggleTimeInput()">
                <option value="all">All Time</option>
                <option value="year">Yearly</option>
                <option value="month">Monthly</option>
                <option value="day">Daily</option>
            </select>
            
            <input type="number" id="time_year" class="filter-input" style="display:none;" placeholder="YYYY" value="{{ date('Y') }}">
            <input type="month" id="time_month" class="filter-input" style="display:none;" value="{{ date('Y-m') }}">
            <input type="date" id="time_day" class="filter-input" style="display:none;" value="{{ date('Y-m-d') }}">
            
            <button class="btn-primary" onclick="fetchDashboardData()">Apply Filter</button>
            <button class="btn-outline" onclick="openCompareModal()">Compare</button>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="chart-row-2">
        <div class="chart-card">
            <div class="chart-header">Request Status Trend</div>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-header">Monthly Spend Trend</div>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="chartMonthly"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="chart-row-2">
        <div class="chart-card">
            <div class="chart-header">Top 10 Vendors by Spend</div>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="chartTopVendors"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-header">Department Performance (Spend)</div>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="chartDeptPerf"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 3 -->
    <div class="chart-row-2">
        <div class="chart-card">
            <div class="chart-header">Order Records by Plant</div>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="chartOrderRecords"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-header">Top 10 Item Catalog</div>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="chartItemCatalog"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 4 -->
    <div class="chart-row-2">
        <div class="chart-card">
            <div class="chart-header">Spend by Plant</div>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="chartPlantSpend"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-header">Service vs Goods (Spend)</div>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="chartServiceGoods"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Drill Down Table Modal -->
<div class="modal-overlay" id="drillModal" onclick="if(event.target===this) { document.getElementById('drillModal').classList.remove('active'); document.body.style.overflow=''; }">
    <div class="modal-content" style="width: 900px;">
        <div class="modal-header">
            <div class="modal-title" id="drillModalTitle">Detail Records</div>
            <button class="modal-close" onclick="document.getElementById('drillModal').classList.remove('active'); document.body.style.overflow='';">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px; align-items:center;">
                <span id="drillModalSubtitle" style="color:#64748b; font-size:13px;"></span>
                <select id="itemsPerPage" class="filter-select" onchange="changeItemsPerPage()">
                    <option value="5">5 per page</option>
                    <option value="10" selected>10 per page</option>
                    <option value="20">20 per page</option>
                    <option value="50">50 per page</option>
                </select>
            </div>
            <table class="data-table" id="drillTable">
                <thead>
                    <tr id="drillTableHead"></tr>
                </thead>
                <tbody id="drillTableBody"></tbody>
            </table>
            <div class="pagination">
                <span id="pageInfo">Showing 0 to 0 of 0 entries</span>
                <div class="page-controls">
                    <button class="page-btn" id="btnPrev" onclick="prevPage()">Previous</button>
                    <button class="page-btn" id="btnNext" onclick="nextPage()">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Compare Config Modal -->
<div class="modal-overlay" id="compareConfigModal" onclick="if(event.target===this) { document.getElementById('compareConfigModal').classList.remove('active'); document.body.style.overflow=''; }">
    <div class="modal-content" style="width: 500px;">
        <div class="modal-header">
            <div class="modal-title">Compare Data</div>
            <button class="modal-close" onclick="document.getElementById('compareConfigModal').classList.remove('active'); document.body.style.overflow='';">&times;</button>
        </div>
        <div class="modal-body">
            <label style="font-weight:600; font-size:13px; color:#374151; display:block; margin-bottom:8px;">Select Topic</label>
            <select id="compareTopic" class="filter-select" style="width:100%; margin-bottom:16px;" onchange="populateCompareEntities()">
                <option value="">-- Choose Topic --</option>
                <option value="topVendors">Vendor Performance (Spend)</option>
                <option value="deptPerf">Department Performance (Spend)</option>
            </select>

            <label style="font-weight:600; font-size:13px; color:#374151; display:block; margin-bottom:8px;">Select Entities (Max 4)</label>
            <div class="checkbox-list" id="compareEntitiesList">
                <div style="color:#9ca3af; font-size:12px; padding:8px;">Select a topic first...</div>
            </div>

            <button class="btn-primary" style="width:100%; margin-top:20px;" onclick="runCompare()">Generate Comparison</button>
        </div>
    </div>
</div>

<!-- Compare Result Modal -->
<div class="modal-overlay" id="compareResultModal" onclick="if(event.target===this) { document.getElementById('compareResultModal').classList.remove('active'); document.body.style.overflow=''; }">
    <div class="modal-content" style="width: 800px;">
        <div class="modal-header">
            <div class="modal-title" id="compareResultTitle">Comparison Result</div>
            <button class="modal-close" onclick="document.getElementById('compareResultModal').classList.remove('active'); document.body.style.overflow='';">&times;</button>
        </div>
        <div class="modal-body">
            <div style="position: relative; height: 350px; width: 100%;">
                <canvas id="chartCompare"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const formatRp = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    const formatShortRp = (num) => {
        if(num >= 1000000000) return 'Rp ' + (num/1000000000).toFixed(1) + 'M';
        if(num >= 1000000) return 'Rp ' + (num/1000000).toFixed(1) + 'Jt';
        if(num >= 1000) return 'Rp ' + (num/1000).toFixed(1) + 'Rb';
        return 'Rp ' + num;
    };

    let charts = {};
    let currentEntities = {}; // Stores raw lists from adminStats

    // Dynamic Filter Inputs
    function toggleTimeInput() {
        const mode = document.getElementById('time_mode').value;
        document.getElementById('time_year').style.display = mode === 'year' ? 'block' : 'none';
        document.getElementById('time_month').style.display = mode === 'month' ? 'block' : 'none';
        document.getElementById('time_day').style.display = mode === 'day' ? 'block' : 'none';
    }
    
    function getFilterParams() {
        const mode = document.getElementById('time_mode').value;
        let val = '';
        if(mode === 'year') val = document.getElementById('time_year').value;
        if(mode === 'month') val = document.getElementById('time_month').value;
        if(mode === 'day') val = document.getElementById('time_day').value;
        return new URLSearchParams({time_mode: mode, time_value: val});
    }

    // Chart Factory
    function createOrUpdateChart(id, type, labels, datasets, clickHandler, isSingleDataset = false) {
        const ctx = document.getElementById(id).getContext('2d');
        if (charts[id]) charts[id].destroy();

        // Standardize datasets if it's single
        let chartDatasets = datasets;
        if (isSingleDataset) {
            chartDatasets = [{
                label: 'Value',
                data: datasets,
                backgroundColor: type === 'doughnut' ? ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#f97316', '#14b8a6', '#6366f1'] : '#3b82f6',
                borderColor: type === 'line' ? '#3b82f6' : '#fff',
                borderWidth: type === 'line' ? 2 : 1,
                fill: false,
                tension: 0.3
            }];
        }

        charts[id] = new Chart(ctx, {
            type: type,
            data: { labels: labels, datasets: chartDatasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                devicePixelRatio: 1, // Force 1:1 scaling to prevent hover offsets on Windows
                plugins: {
                    legend: { display: !isSingleDataset || type === 'doughnut', position: 'top' }, // Legends hidden for single variables
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                
                                let value = context.parsed;
                                if (value !== null && typeof value === 'object') {
                                    value = value.y !== undefined ? value.y : value.x;
                                }
                                
                                if (id !== 'chartStatus' && id !== 'chartOrderRecords') {
                                    label += formatRp(value);
                                } else {
                                    label += value;
                                }
                                return label;
                            }
                        }
                    }
                },
                onClick: (evt, activeEls) => {
                    if(activeEls.length > 0 && clickHandler) {
                        const index = activeEls[0].index;
                        const datasetIndex = activeEls[0].datasetIndex;
                        const seriesLabel = chartDatasets[datasetIndex] ? chartDatasets[datasetIndex].label : '';
                        clickHandler(labels[index], seriesLabel);
                    }
                }
            }
        });
    }

    // Fetch Main Dashboard
    function fetchDashboardData() {
        fetch('{{ route("api.dashboard.admin") }}?' + getFilterParams().toString())
            .then(res => res.json())
            .then(data => {
                const c = data.charts;
                currentEntities = data.entities;

                // 1. Status Trend
                createOrUpdateChart('chartStatus', 'line', c.status.labels, c.status.datasets, (lbl, series) => openDrillModal('status', lbl, series, 'Request Status'));
                // 2. Monthly Spend
                createOrUpdateChart('chartMonthly', 'line', c.monthlySpend.labels, c.monthlySpend.data, (lbl) => openDrillModal('monthlySpend', lbl, '', 'Monthly Spend'), true);
                // 3. Top Vendors
                createOrUpdateChart('chartTopVendors', 'bar', c.topVendors.labels, c.topVendors.data, (lbl) => openDrillModal('topVendors', lbl, '', 'Vendor Details'), true);
                // 4. Dept Perf
                createOrUpdateChart('chartDeptPerf', 'bar', c.deptPerf.labels, c.deptPerf.data, (lbl) => openDrillModal('deptPerf', lbl, '', 'Department Details'), true);
                // 5. Order Records
                createOrUpdateChart('chartOrderRecords', 'bar', c.orderRecords.labels, c.orderRecords.data, (lbl) => openDrillModal('orderRecords', lbl, '', 'Order Records by Plant'), true);
                // 6. Item Catalog
                createOrUpdateChart('chartItemCatalog', 'bar', c.itemCatalog.labels, c.itemCatalog.data, (lbl) => openDrillModal('itemCatalog', lbl, '', 'Item Catalog Details'), true);
                // 7. Plant Spend
                createOrUpdateChart('chartPlantSpend', 'doughnut', c.plantSpend.labels, c.plantSpend.data, (lbl) => openDrillModal('plantSpend', lbl, '', 'Spend by Plant'), true);
                // 8. Service vs Goods
                createOrUpdateChart('chartServiceGoods', 'doughnut', c.serviceGoods.labels, c.serviceGoods.data, (lbl) => openDrillModal('serviceGoods', lbl, '', 'Service vs Goods Details'), true);
            });
    }

    // ==========================================
    // Drill Down Data Table Logic
    // ==========================================
    let tableData = [];
    let filteredData = [];
    let currentPage = 1;
    let itemsPerPage = 10;
    let sortCol = null;
    let sortAsc = true;

    const headersMap = {
        'status': ['Document No', 'Title', 'Department', 'Status', 'Date'],
        'monthlySpend': ['Document No', 'Department', 'Plant', 'Value', 'Date'],
        'topVendors': ['Vendor Name', 'Location', 'Document No', 'Value', 'Date'],
        'deptPerf': ['Document No', 'Title', 'Status', 'Value', 'Date'],
        'orderRecords': ['Document No', 'Title', 'Department', 'Status', 'Date'],
        'itemCatalog': ['Item Name', 'Vendor', 'Qty', 'Value', 'Date'],
        'plantSpend': ['Document No', 'Title', 'Department', 'Value', 'Date'],
        'serviceGoods': ['Document No', 'Title', 'Department', 'Value', 'Date']
    };

    function openDrillModal(type, label, series, title) {
        document.getElementById('drillModalTitle').innerText = title + ' - ' + label + (series && series !== 'Value' ? ` (${series})` : '');
        document.getElementById('drillModalSubtitle').innerText = 'Loading data...';
        document.getElementById('drillTableHead').innerHTML = '';
        document.getElementById('drillTableBody').innerHTML = '';
        document.body.style.overflow = 'hidden';
        document.getElementById('drillModal').classList.add('active');

        // Render Headers
        const headers = headersMap[type];
        let theadHtml = '';
        headers.forEach((h, idx) => {
            theadHtml += `<th onclick="sortTable('col${idx+1}')">${h} <span id="sort-ind-col${idx+1}"></span></th>`;
        });
        document.getElementById('drillTableHead').innerHTML = theadHtml;

        const params = getFilterParams();
        params.append('type', type);
        params.append('label', label);
        if(series && series !== 'Value') params.append('series', series);

        fetch('{{ route("api.dashboard.drilldown") }}?' + params.toString())
            .then(res => res.json())
            .then(data => {
                tableData = data.rows;
                filteredData = [...tableData];
                currentPage = 1;
                sortCol = null;
                document.getElementById('drillModalSubtitle').innerText = `Found ${tableData.length} records.`;
                renderTable();
            });
    }

    function renderTable() {
        const tbody = document.getElementById('drillTableBody');
        tbody.innerHTML = '';
        
        if (filteredData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No records found.</td></tr>';
            document.getElementById('pageInfo').innerText = 'Showing 0 to 0 of 0 entries';
            document.getElementById('btnPrev').disabled = true;
            document.getElementById('btnNext').disabled = true;
            return;
        }

        const start = (currentPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, filteredData.length);
        const pageData = filteredData.slice(start, end);

        pageData.forEach(row => {
            tbody.innerHTML += `
                <tr>
                    <td>${row.col1}</td>
                    <td>${row.col2}</td>
                    <td>${row.col3}</td>
                    <td>${row.col4}</td>
                    <td>${row.col5}</td>
                </tr>
            `;
        });

        document.getElementById('pageInfo').innerText = `Showing ${start + 1} to ${end} of ${filteredData.length} entries`;
        document.getElementById('btnPrev').disabled = currentPage === 1;
        document.getElementById('btnNext').disabled = end >= filteredData.length;
    }

    function changeItemsPerPage() {
        itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);
        currentPage = 1;
        renderTable();
    }

    function prevPage() { if(currentPage > 1) { currentPage--; renderTable(); } }
    function nextPage() { if(currentPage * itemsPerPage < filteredData.length) { currentPage++; renderTable(); } }

    function sortTable(col) {
        if (sortCol === col) { sortAsc = !sortAsc; } 
        else { sortCol = col; sortAsc = true; }

        // reset indicators
        for(let i=1; i<=5; i++) {
            let el = document.getElementById(`sort-ind-col${i}`);
            if(el) el.innerText = '';
        }
        let currentInd = document.getElementById(`sort-ind-${col}`);
        if(currentInd) currentInd.innerText = sortAsc ? ' ▲' : ' ▼';

        filteredData.sort((a, b) => {
            let valA = a[col] || '';
            let valB = b[col] || '';
            // Handle numeric sort for Value if applicable (very naive check)
            if(typeof valA === 'string' && valA.startsWith('Rp ')) {
                valA = parseInt(valA.replace(/\D/g, '')) || 0;
                valB = parseInt(valB.replace(/\D/g, '')) || 0;
                return sortAsc ? valA - valB : valB - valA;
            }
            if (valA < valB) return sortAsc ? -1 : 1;
            if (valA > valB) return sortAsc ? 1 : -1;
            return 0;
        });
        currentPage = 1;
        renderTable();
    }

    // ==========================================
    // Compare Logic
    // ==========================================
    function openCompareModal() {
        document.getElementById('compareTopic').value = '';
        document.getElementById('compareEntitiesList').innerHTML = '<div style="color:#9ca3af; font-size:12px; padding:8px;">Select a topic first...</div>';
        document.body.style.overflow = 'hidden';
        document.getElementById('compareConfigModal').classList.add('active');
    }

    function populateCompareEntities() {
        const topic = document.getElementById('compareTopic').value;
        const listDiv = document.getElementById('compareEntitiesList');
        listDiv.innerHTML = '';
        if(!topic) return;

        const entities = currentEntities[topic] || [];
        if(entities.length === 0) {
            listDiv.innerHTML = '<div style="font-size:13px; padding:8px;">No data available to compare.</div>';
            return;
        }

        entities.forEach(ent => {
            listDiv.innerHTML += `
                <label class="checkbox-item">
                    <input type="checkbox" value="${ent}" class="compare-chk" onchange="limitCompareSelection(this)">
                    <span style="font-size:13px;">${ent}</span>
                </label>
            `;
        });
    }

    function limitCompareSelection(chk) {
        const checked = document.querySelectorAll('.compare-chk:checked');
        if (checked.length > 4) {
            chk.checked = false;
            alert('You can only select up to 4 entities to compare.');
        }
    }

    function runCompare() {
        const topic = document.getElementById('compareTopic').value;
        const checked = Array.from(document.querySelectorAll('.compare-chk:checked')).map(c => c.value);

        if(!topic || checked.length === 0) {
            alert('Please select a topic and at least one entity.');
            return;
        }

        const params = getFilterParams();
        params.append('topic', topic);
        params.append('entities', JSON.stringify(checked));

        fetch('{{ route("api.dashboard.compare") }}?' + params.toString())
            .then(res => res.json())
            .then(data => {
                document.getElementById('compareConfigModal').classList.remove('active');
                document.getElementById('compareResultTitle').innerText = 'Comparison: ' + (topic==='topVendors' ? 'Vendors' : 'Departments');
                document.getElementById('compareResultModal').classList.add('active');
                
                // Render Compare Chart
                createOrUpdateChart('chartCompare', 'bar', data.labels, data.data, null, true);
                if(charts['chartCompare']) {
                    charts['chartCompare'].data.datasets[0].backgroundColor = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b'];
                    charts['chartCompare'].data.datasets[0].label = 'Total Spend';
                    charts['chartCompare'].update();
                }
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        toggleTimeInput();
        fetchDashboardData();

        // Fix for container resize (e.g. sidebar toggles) causing Chart.js hover offsets
        const dashContainer = document.querySelector('.dash-container');
        if (dashContainer) {
            const resizeObserver = new ResizeObserver(() => {
                for (let id in charts) {
                    if (charts[id]) {
                        charts[id].resize();
                    }
                }
            });
            resizeObserver.observe(dashContainer);
        }
    });
</script>
@endsection