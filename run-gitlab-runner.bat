@echo off
echo ==========================================
echo  GitLab Runner - Dashboard Kepegawaian PU
echo ==========================================
echo.
echo Menjalankan GitLab Runner...
echo Config: %~dp0config.toml
echo.
echo [INFO] Runner akan berjalan di foreground.
echo [INFO] Tutup window ini untuk menghentikan runner.
echo.
C:\GitLab-Runner\gitlab-runner.exe run --config "%~dp0config.toml"
pause
