# Fan Hub+ — development launcher
# Starts MariaDB (if needed) and the Laravel dev server on http://127.0.0.1:8000
#
#   Powershell:  .\start-dev.ps1
#   Then open:   http://127.0.0.1:8000

$ErrorActionPreference = 'Stop'
Set-Location -Path $PSScriptRoot

# 1. PHP + Composer + MariaDB toolchain -------------------------------------
$phpDir = (Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Directory -Filter "PHP.PHP.8.3*" -ErrorAction SilentlyContinue | Select-Object -First 1).FullName
if (-not $phpDir) { Write-Error "PHP 8.3 not found. Install with: winget install --id PHP.PHP.8.3"; exit 1 }
$env:PATH = "$phpDir;$env:LOCALAPPDATA\composer;$env:PATH"

# 2. MariaDB (start as a plain process when no Windows service exists) -------
$mariaPkg = Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Directory -Filter "MariaDB.Server*" -ErrorAction SilentlyContinue | Select-Object -First 1
$mariaBin = if ($mariaPkg) { Get-ChildItem $mariaPkg.FullName -Recurse -Filter "mariadbd.exe" -ErrorAction SilentlyContinue | Select-Object -First 1 } else { $null }
if ($mariaBin) {
    $listening = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue
    if (-not $listening) {
        $dataDir = Join-Path $mariaBin.DirectoryName '..\data'
        $myIni = Join-Path $dataDir 'my.ini'
        if (Test-Path $myIni) {
            Write-Host "[start-dev] Starting MariaDB..." -ForegroundColor Cyan
            Start-Process -FilePath $mariaBin.FullName -ArgumentList "--defaults-file=`"$myIni`"", '--console' -WindowStyle Hidden
            Start-Sleep -Seconds 5
        }
    }
    Write-Host "[start-dev] MariaDB is listening on :3306" -ForegroundColor Green
} else {
    Write-Warning "MariaDB not found - make sure a MySQL-compatible server is running on :3306"
}

# 3. Environment check -------------------------------------------------------
if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
    php artisan key:generate --force
    Write-Host "[start-dev] Created .env from .env.example" -ForegroundColor Yellow
}

# 4. Migrations --------------------------------------------------------------
php artisan migrate --force

# 5. Storage link (uploads) --------------------------------------------------
if (-not (Test-Path 'public/storage')) { php artisan storage:link }

# 6. Serve -------------------------------------------------------------------
Write-Host ""
Write-Host "  FAN HUB+  -  Explore. Discover. Connect." -ForegroundColor Magenta
Write-Host "  App:  http://127.0.0.1:8000" -ForegroundColor White
Write-Host "  Admin login: admin@fanhubplus.test / FanHub!Admin2026 (development seed)" -ForegroundColor Gray
Write-Host ""
php artisan serve --host=127.0.0.1 --port=8000
