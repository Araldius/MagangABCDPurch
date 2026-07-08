import subprocess, sys

path = "resources/views/purchase_requests/list.blade.php"

with open(path, "r", encoding="utf-8") as f:
    content = f.read()

changed = 0

old1 = "'submitted'        => [\\$isPurchasing ? 'Awaiting Approval' : 'Purchasing Approval', '#fef3c7', '#d97706', '#f59e0b'],"
new1 = "'submitted'        => [$isPurchasing ? 'Awaiting Approval' : 'Purchasing Approval', '#fef3c7', '#d97706', '#f59e0b'],"
if old1 in content:
    content = content.replace(old1, new1, 1)
    changed += 1
else:
    print("Marker 1 (statusCfg) tidak ditemukan persis, cek manual.")

old2 = "<option value=\"submitted\">{{ \\$isPurchasing ? 'Awaiting Approval' : 'Purchasing Approval' }}</option>"
new2 = "<option value=\"submitted\">{{ $isPurchasing ? 'Awaiting Approval' : 'Purchasing Approval' }}</option>"
if old2 in content:
    content = content.replace(old2, new2, 1)
    changed += 1
else:
    print("Marker 2 (dropdown) tidak ditemukan persis, cek manual.")

if changed == 0:
    print("Tidak ada yang diubah. Menampilkan baris 1-15 untuk dicek manual:")
    lines = content.split("\n")
    for i, line in enumerate(lines[:15], start=1):
        print(i, line)
    sys.exit(1)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print(f"BERHASIL: {changed} bagian diperbaiki di {path}")
