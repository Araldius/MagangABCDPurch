import re

with open('resources/views/purchase_requests/list.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Add isAdmin
admin_var = "const isAdmin = @json(auth()->check() && auth()->user()->role === 'admin');\n\nconst allPRs = "
content = content.replace("const allPRs = ", admin_var)

# Add saveAdminNote function
script_to_add = """
function saveAdminNote(id, type, note) {
    fetch('{{ route("requests.item_note") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ item_id: id, type: type, note: note })
    }).then(res => {
        if (!res.ok) alert('Failed to save admin note');
    });
}
"""
content = content.replace("function prSort(col)", script_to_add + "\nfunction prSort(col)")

# Update Service Request Table Headers
sr_th_old = """                        <th style="${thS};width:36px">NO</th>
                        <th style="${thS}">ITEM ID</th>
                        <th style="${thS}">ITEM NAME</th>
                        <th style="${thS}">NOTES</th>
                        <th style="${thS}">SPEC</th>
                        <th style="${thS};text-align:right;width:60px">QTY</th>
                        <th style="${thS};width:55px">UNIT</th>
                        ${extraTh}
                    </tr></thead>"""

sr_th_new = """                        <th style="${thS};width:36px">NO</th>
                        <th style="${thS}">ITEM ID</th>
                        <th style="${thS}">ITEM NAME</th>
                        <th style="${thS}">NOTES</th>
                        <th style="${thS}">SPEC</th>
                        <th style="${thS};text-align:right;width:60px">QTY</th>
                        <th style="${thS};width:55px">UNIT</th>
                        ${extraTh}
                        ${isAdmin ? `<th style="${thS};width:150px">ADMIN NOTES</th>` : ''}
                    </tr></thead>"""
content = content.replace(sr_th_old, sr_th_new)

# Update Service Request Table Body
sr_tbody_old = """                    ${hasVS ? `
                    <td style="${tdS};font-family:monospace;font-weight:600;color:#111827;text-align:right;">${vs ? fmtRp(vs.unit_price) : '-'}</td>
                    <td style="${tdS};font-family:monospace;font-weight:700;color:#111827;text-align:right;">${vs ? fmtRp(vs.total) : '-'}</td>
                    <td style="${tdS}">
                        ${vs ? `<span style="padding:2px 8px;background:#e0f2fe;border-radius:4px;font-size:11px;font-weight:600;color:#0369a1;white-space:nowrap;">${vs.vendor}</span>` : '-'}
                    </td>` : ''}
                </tr>`;"""

sr_tbody_new = """                    ${hasVS ? `
                    <td style="${tdS};font-family:monospace;font-weight:600;color:#111827;text-align:right;">${vs ? fmtRp(vs.unit_price) : '-'}</td>
                    <td style="${tdS};font-family:monospace;font-weight:700;color:#111827;text-align:right;">${vs ? fmtRp(vs.total) : '-'}</td>
                    <td style="${tdS}">
                        ${vs ? `<span style="padding:2px 8px;background:#e0f2fe;border-radius:4px;font-size:11px;font-weight:600;color:#0369a1;white-space:nowrap;">${vs.vendor}</span>` : '-'}
                    </td>` : ''}
                    ${isAdmin ? `
                    <td style="${tdS}">
                        <input type="text" class="form-control" style="font-size:11px;padding:4px;border:1px solid #e5e7eb;border-radius:4px;width:100%" placeholder="Add note..." value="${it.admin_notes || ''}" onchange="saveAdminNote(${it.id}, 'service', this.value)">
                    </td>
                    ` : ''}
                </tr>`;"""
content = content.replace(sr_tbody_old, sr_tbody_new)


# Update Goods Request Table Headers
pr_th_old = """                        <th style="${thS}">NO</th>
                        <th style="${thS}">ITEM ID</th>
                        <th style="${thS}">ITEM NAME</th>
                        <th style="${thS}">NOTES</th>
                        <th style="${thS}">SPEC</th>
                        <th style="${thS};text-align:right">QTY</th>
                        <th style="${thS}">UNIT</th>
                        ${gTh}
                    </tr></thead>"""

pr_th_new = """                        <th style="${thS}">NO</th>
                        <th style="${thS}">ITEM ID</th>
                        <th style="${thS}">ITEM NAME</th>
                        <th style="${thS}">NOTES</th>
                        <th style="${thS}">SPEC</th>
                        <th style="${thS};text-align:right">QTY</th>
                        <th style="${thS}">UNIT</th>
                        ${gTh}
                        ${isAdmin ? `<th style="${thS};width:150px">ADMIN NOTES</th>` : ''}
                    </tr></thead>"""
content = content.replace(pr_th_old, pr_th_new)

# Update Goods Request Table Body
pr_tbody_old = """                ${hasPriceCol ? `
                <td style="${tdS};font-family:monospace;font-weight:600">${vs ? fmtRp(vs.unit_price) : '—'}</td>
                <td style="${tdS};font-family:monospace;font-weight:700;color:#111827">${vs ? fmtRp(vs.total) : '—'}</td>
                <td style="${tdS}">${vs ? `<span style="padding:2px 8px;background:#e0f2fe;border-radius:4px;font-size:11px;font-weight:600;color:#0369a1;white-space:nowrap">${vs.vendor}</span>` : '—'}</td>` : ''}
            </tr>`;"""

pr_tbody_new = """                ${hasPriceCol ? `
                <td style="${tdS};font-family:monospace;font-weight:600">${vs ? fmtRp(vs.unit_price) : '—'}</td>
                <td style="${tdS};font-family:monospace;font-weight:700;color:#111827">${vs ? fmtRp(vs.total) : '—'}</td>
                <td style="${tdS}">${vs ? `<span style="padding:2px 8px;background:#e0f2fe;border-radius:4px;font-size:11px;font-weight:600;color:#0369a1;white-space:nowrap">${vs.vendor}</span>` : '—'}</td>` : ''}
                ${isAdmin ? `
                <td style="${tdS}">
                    <input type="text" class="form-control" style="font-size:11px;padding:4px;border:1px solid #e5e7eb;border-radius:4px;width:100%" placeholder="Add note..." value="${it.admin_notes || ''}" onchange="saveAdminNote(${it.id}, 'goods', this.value)">
                </td>
                ` : ''}
            </tr>`;"""
content = content.replace(pr_tbody_old, pr_tbody_new)


# Fix colspan on empty states
content = content.replace('${hasVS ? 10 : 7}', '${hasVS ? (isAdmin ? 11 : 10) : (isAdmin ? 8 : 7)}')
content = content.replace('<td colspan="7"', '<td colspan="${isAdmin ? 8 : 7}"')

with open('resources/views/purchase_requests/list.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
