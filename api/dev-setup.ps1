<#
Dev setup script for Lumen docker environment
Usage: Run from project root or this folder in PowerShell (requires Docker Desktop)
#>
Set-StrictMode -Version Latest
$here = Split-Path -Parent $MyInvocation.MyCommand.Definition
Push-Location $here
try {
    if (!(Test-Path .env)) { Copy-Item .env.example .env -Force; Write-Output "Copied .env.example -> .env" }
    Write-Output "Starting docker-compose (detached)..."
    docker-compose up --build -d
    Write-Output "Waiting for DB to be ready (sleep 6s)..."
    Start-Sleep -Seconds 6
    Write-Output "Applying SQL migrations to MySQL container..."
    docker-compose exec -T db mysql -u root -prootpass liga < database/migrations/001_create_users.sql
    docker-compose exec -T db mysql -u root -prootpass liga < database/migrations/002_create_athletes.sql
    docker-compose exec -T db mysql -u root -prootpass liga < database/migrations/003_create_clubs.sql
    docker-compose exec -T db mysql -u root -prootpass liga < database/migrations/004_create_tournaments.sql
    Write-Output "Migrations applied. App: http://localhost:8080 | Adminer: http://localhost:8081"
} catch {
    Write-Error "Error during setup: $_"
} finally { Pop-Location }
