import subprocess

result = subprocess.run(
    ["grep", "-rn", "-i", "master.item\|master-item\|master_item", "--include=*.php", "."],
    capture_output=True, text=True
)
print(result.stdout[:4000] if result.stdout else "Tidak ditemukan.")
