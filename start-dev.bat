@echo off
echo Starting XAMPP Apache and MySQL...
start "" "C:\xampp\xampp_start.exe"

echo Waiting for services to start...
timeout /t 3 /nobreak >nul

echo Starting Node server + ngrok...
cd /d "%~dp0Requesitor\Node"
npm run all
