@echo off
REM Abre el panel de caché con timestamp único para evitar caché del navegador
powershell -Command "$timestamp = Get-Date -Format 'yyyyMMddHHmmss'; Start-Process 'http://localhost/cursos/cache-admin.php?v=$timestamp'"
