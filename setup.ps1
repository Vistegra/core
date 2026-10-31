param (
    [switch]$InitRoot = $false
)

$ErrorActionPreference = "Stop"

$CoreDir = $PSScriptRoot
$ProjectRoot = Split-Path -Parent $CoreDir

Write-Host "=== [1/6] Подготовка директорий проекта: $ProjectRoot ===" -ForegroundColor Cyan
$Dirs = @(
    "$CoreDir\assets\css\module",
    "$CoreDir\assets\js\libs",
    "$CoreDir\assets\js\module",
    "$ProjectRoot\public\css\test",
    "$ProjectRoot\public\js\test",
    "$ProjectRoot\public\images",
    "$ProjectRoot\shared\upload"
)
foreach ($Dir in $Dirs) {
    if (!(Test-Path $Dir)) {
        New-Item -ItemType Directory -Force -Path $Dir | Out-Null
        Write-Host "  + Создана папка: $Dir" -ForegroundColor Gray
    }
}

# Опциональная распаковка шаблона нового проекта из core/root (если запущен с флагом -InitRoot)
if ($InitRoot) {
    Write-Host "=== Распаковка базовых файлов из core/root ===" -ForegroundColor Yellow
    Get-ChildItem -Path "$CoreDir\root" | ForEach-Object {
        $Target = Join-Path $ProjectRoot $_.Name
        if (!(Test-Path $Target)) {
            Copy-Item -Path $_.FullName -Destination $Target -Recurse
            Write-Host "  + Скопирован: $($_.Name)" -ForegroundColor Gray
        }
    }
}

Write-Host "=== [2/6] Установка глобальных зависимостей Webpack в корень проекта ===" -ForegroundColor Cyan
$RootPkg = "$ProjectRoot\package.json"
if (!(Test-Path $RootPkg)) {
    Copy-Item "$CoreDir\src\packageGlobal.json" $RootPkg
    Write-Host "  + Скопирован packageGlobal.json -> package.json" -ForegroundColor Gray
}
Push-Location $ProjectRoot
npm install --legacy-peer-deps
Pop-Location

Write-Host "=== [3/6] Установка PHP-библиотек (Composer в core/libs) ===" -ForegroundColor Cyan
Push-Location "$CoreDir\libs"
if (Get-Command php -ErrorAction SilentlyContinue) {
    php composer.phar install --ignore-platform-reqs --no-interaction
} else {
    Write-Host "  ! PHP не найден в PATH хоста, запускаем composer через Docker..." -ForegroundColor Yellow
    docker compose exec -T app composer install --working-dir=/var/www/html/core/libs --ignore-platform-reqs --no-interaction
}
Pop-Location

Write-Host "=== [4/6] Сборка ассетов Ядра (core/src) ===" -ForegroundColor Cyan
Push-Location "$CoreDir\src"
npm install --legacy-peer-deps
npm run coreProd
npm run createResourceFile
npm run coreModuleProd
Pop-Location

Write-Host "=== [5/6] Сборка ассетов Калькулятора (public/src) ===" -ForegroundColor Cyan
if (Test-Path "$ProjectRoot\public\src\package.json") {
    Push-Location "$ProjectRoot\public\src"
    npm install --legacy-peer-deps
    # Собираем dev-бандл (calculator.js и style.css) для 127.0.0.1
    npm run publicDev
    # Собираем prod-бандл (calculator-<hash>.min.js) и обновляем хеш
    npm run publicProd
    Pop-Location
}

Write-Host "=== [6/6] Готово! Все ассеты ядра и проекта собраны. ===" -ForegroundColor Green