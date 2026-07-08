import subprocess, sys

result = subprocess.run(
    ["grep", "-rl", "PR & SR List", "."],
    capture_output=True, text=True
)
paths = [p for p in result.stdout.strip().split("\n") if p]

if not paths:
    print("File dengan 'PR & SR List' tidak ditemukan.")
    sys.exit(1)

path = paths[0]
print("File ditemukan di:", path)

with open(path, "r", encoding="utf-8") as f:
    content = f.read()

changed = 0

old_cfg = "'submitted'        => ['Awaiting Approval', '#fef3c7', '#d97706', '#f59e0b'],"
new_cfg = "'submitted'        => [\$isPurchasing ? 'Awaiting Approval' : 'Purchasing Approval', '#fef3c7', '#d97706', '#f59e0b'],"
if old_cfg in content:
    content = content.replace(old_cfg, new_cfg, 1)
    changed += 1
else:
    print("PERINGATAN: marker \$statusCfg tidak ditemukan.")

old_opt = '<option value="submitted">Awaiting Approval</option>'
new_opt = "<option value=\"submitted\">{{ \$isPurchasing ? 'Awaiting Approval' : 'Purchasing Approval' }}</option>"
if old_opt in content:
    content = content.replace(old_opt, new_opt, 1)
    changed += 1
else:
    print("PERINGATAN: marker dropdown filter tidak ditemukan.")

if changed == 0:
    print("Tidak ada yang diubah.")
    sys.exit(1)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print(f"BERHASIL: {changed} bagian diubah di {path}")
