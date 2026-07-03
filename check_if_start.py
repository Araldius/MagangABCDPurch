import subprocess

result = subprocess.run(["grep", "-n", "@if\|@auth\|@endif\|Procurement History", "resources/views/layouts/app.blade.php"], capture_output=True, text=True)
print(result.stdout)
