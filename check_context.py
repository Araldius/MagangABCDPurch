import subprocess

print("=== routes/web.php (baris 45-75) ===")
result = subprocess.run(["sed", "-n", "45,75p", "routes/web.php"], capture_output=True, text=True)
print(result.stdout)

print("\n=== resources/views/layouts/app.blade.php (baris 130-155) ===")
result2 = subprocess.run(["sed", "-n", "130,155p", "resources/views/layouts/app.blade.php"], capture_output=True, text=True)
print(result2.stdout)
