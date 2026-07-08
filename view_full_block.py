import subprocess
result = subprocess.run(["sed", "-n", "113,155p", "resources/views/layouts/app.blade.php"], capture_output=True, text=True)
print(result.stdout)
