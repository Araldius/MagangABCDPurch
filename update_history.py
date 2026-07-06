import re

with open('resources/views/history/orders.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add Plant filter next to Department filter
dept_filter = """              <select id="unit-filter" onchange="applyHFilters()" style="height:34px;border:1px solid #d1d5db;border-radius:6px;padding:0 12px;font-size:12.5px;outline:none;background:#fff">
                  <option value="">All Departments</option>
                  @foreach($records->pluck('department')->filter()->unique() as $d)
                      <option value="{{ $d }}">{{ $d }}</option>
                  @endforeach
              </select>"""

plant_filter = dept_filter + """
              <select id="plant-filter" onchange="applyHFilters()" style="height:34px;border:1px solid #d1d5db;border-radius:6px;padding:0 12px;font-size:12.5px;outline:none;background:#fff">
                  <option value="">All Plants</option>
                  @foreach($records->pluck('plant')->filter(fn($p) => $p !== '-')->unique() as $p)
                      <option value="{{ $p }}">{{ $p }}</option>
                  @endforeach
              </select>"""
content = content.replace(dept_filter, plant_filter)

# 2. Add Plant column to table header
header_old = """                      <th onclick="histSort(2)" style="padding:12px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">DEPARTMENT <span id="hs2">↕</span></th>
                      <th onclick="histSort(3)" style="padding:12px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">TOTAL VALUE <span id="hs3">↕</span></th>"""

header_new = """                      <th onclick="histSort(2)" style="padding:12px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">DEPARTMENT <span id="hs2">↕</span></th>
                      <th onclick="histSort(3)" style="padding:12px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">PLANT <span id="hs3">↕</span></th>
                      <th onclick="histSort(4)" style="padding:12px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">TOTAL VALUE <span id="hs4">↕</span></th>"""
content = content.replace(header_old, header_new)

# Update sort ids for the rest
content = content.replace("histSort(3)", "histSort(33)")
content = content.replace("hs3", "hs33")
content = content.replace("histSort(4)", "histSort(3)")
content = content.replace("hs4", "hs3")
content = content.replace("histSort(33)", "histSort(4)")
content = content.replace("hs33", "hs4")

# 3. Add Plant to tr data and cells
tr_old = """                  <tr style="border-bottom:1px solid #f3f4f6" data-dept="{{ $r->department }}" data-date="{{ $r->completed_date_raw ?? '' }}">
                      <td style="padding:12px 20px;font-weight:600;color:#111827;font-family:monospace">{{ $r->doc_number }}</td>
                      <td style="padding:12px 14px">
                          <div style="font-weight:600;color:#111827">{{ $r->vendor_name }}</div>
                      </td>
                      <td style="padding:12px 14px;color:#6b7280">{{ $r->department }}</td>
                      <td style="padding:12px 14px;font-family:monospace;font-weight:600;color:#111827">Rp{{ number_format($r->total_value,0,',','.') }}</td>"""

tr_new = """                  <tr style="border-bottom:1px solid #f3f4f6" data-dept="{{ $r->department }}" data-plant="{{ $r->plant }}" data-date="{{ $r->completed_date_raw ?? '' }}">
                      <td style="padding:12px 20px;font-weight:600;color:#111827;font-family:monospace">{{ $r->doc_number }}</td>
                      <td style="padding:12px 14px">
                          <div style="font-weight:600;color:#111827">{{ $r->vendor_name }}</div>
                      </td>
                      <td style="padding:12px 14px;color:#6b7280">{{ $r->department }}</td>
                      <td style="padding:12px 14px;color:#6b7280">{{ $r->plant }}</td>
                      <td style="padding:12px 14px;font-family:monospace;font-weight:600;color:#111827">Rp{{ number_format($r->total_value,0,',','.') }}</td>"""
content = content.replace(tr_old, tr_new)

# 4. Update applyHFilters
filter_old = """    function applyHFilters() {
      const q      = (document.getElementById('hist-search')?.value || '').toLowerCase();
      const dept   = document.getElementById('unit-filter')?.value || '';
      const status = document.getElementById('hist-status-filter')?.value || '';
      const dStart = document.getElementById('hist-start-date')?.value;
      const dEnd   = document.getElementById('hist-end-date')?.value;

      let rows = Array.from(document.querySelectorAll('#hist-tbody tr[data-dept]'));

      let filtered = rows.filter(r => {
          if (dept   && r.dataset.dept   !== dept)   return false;
          if (status && r.dataset.status !== status) return false;
          if (q && !r.textContent.toLowerCase().includes(q)) return false;
          if (dStart && r.dataset.date < dStart) return false;
          if (dEnd && r.dataset.date > dEnd) return false;
          return true;
      });"""

filter_new = """    function applyHFilters() {
      const q      = (document.getElementById('hist-search')?.value || '').toLowerCase();
      const dept   = document.getElementById('unit-filter')?.value || '';
      const plant  = document.getElementById('plant-filter')?.value || '';
      const status = document.getElementById('hist-status-filter')?.value || '';
      const dStart = document.getElementById('hist-start-date')?.value;
      const dEnd   = document.getElementById('hist-end-date')?.value;

      let rows = Array.from(document.querySelectorAll('#hist-tbody tr[data-dept]'));

      let filtered = rows.filter(r => {
          if (dept   && r.dataset.dept   !== dept)   return false;
          if (plant  && r.dataset.plant  !== plant)  return false;
          if (status && r.dataset.status !== status) return false;
          if (q && !r.textContent.toLowerCase().includes(q)) return false;
          if (dStart && r.dataset.date < dStart) return false;
          if (dEnd && r.dataset.date > dEnd) return false;
          return true;
      });"""
content = content.replace(filter_old, filter_new)


# 5. Fix modal display
modal_old = """      document.getElementById('modal-info-department').textContent = pr.department;"""
modal_new = """      document.getElementById('modal-info-department').textContent = pr.department + ' | ' + (pr.plant || '-');"""
content = content.replace(modal_old, modal_new)

# 6. colspan fix for empty state
content = content.replace('<td colspan="7"', '<td colspan="8"')

with open('resources/views/history/orders.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
