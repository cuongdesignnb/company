param(
    [string]$ProjectDir = ""
)

$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

if (-not $ProjectDir) {
    $ProjectDir = Split-Path -Parent $PSScriptRoot
}

Set-Location $ProjectDir

$projectRoot = [System.IO.Path]::GetFullPath($ProjectDir).TrimEnd('\')
$distDir = Join-Path $projectRoot "dist"
$stagingRoot = Join-Path $projectRoot ".tmp\wordpress-packages"

foreach ($path in @($distDir, $stagingRoot)) {
    $resolved = [System.IO.Path]::GetFullPath($path)
    if (-not $resolved.StartsWith($projectRoot + '\', [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Duong dan build nam ngoai project: $resolved"
    }
}

if (Test-Path $stagingRoot) {
    Remove-Item -LiteralPath $stagingRoot -Recurse -Force
}

New-Item -ItemType Directory -Force -Path $distDir, $stagingRoot | Out-Null

$packages = [ordered]@{
    "cb-company-theme" = "wp-content\themes\cb-company-theme"
    "cb-company-core" = "wp-content\plugins\cb-company-core"
    "cb-webp-converter" = "wp-content\plugins\cb-webp-converter"
    "cb-site-transfer" = "wp-content\plugins\cb-site-transfer"
}

$excludedDirectories = @(
    ".git",
    ".github",
    "node_modules",
    "tests",
    "test",
    "backups",
    ".tmp"
)

$excludedPatterns = @(
    "*.log",
    "*.map",
    "*.sql",
    "*.tar",
    "*.tar.gz",
    "*.zip",
    ".DS_Store",
    "Thumbs.db"
)

foreach ($package in $packages.GetEnumerator()) {
    $source = Join-Path $projectRoot $package.Value
    if (-not (Test-Path $source)) {
        throw "Khong tim thay source package: $source"
    }

    $stagingPackage = Join-Path $stagingRoot $package.Key
    Copy-Item -LiteralPath $source -Destination $stagingPackage -Recurse -Force

    Get-ChildItem -LiteralPath $stagingPackage -Directory -Recurse -Force |
        Where-Object { $excludedDirectories -contains $_.Name } |
        Sort-Object FullName -Descending |
        ForEach-Object {
            $candidate = [System.IO.Path]::GetFullPath($_.FullName)
            if (-not $candidate.StartsWith([System.IO.Path]::GetFullPath($stagingPackage) + '\', [System.StringComparison]::OrdinalIgnoreCase)) {
                throw "Tu choi xoa duong dan ngoai staging: $candidate"
            }
            Remove-Item -LiteralPath $candidate -Recurse -Force
        }

    foreach ($pattern in $excludedPatterns) {
        Get-ChildItem -LiteralPath $stagingPackage -File -Recurse -Force -Filter $pattern |
            ForEach-Object { Remove-Item -LiteralPath $_.FullName -Force }
    }

    $destination = Join-Path $distDir ($package.Key + ".zip")
    Compress-Archive -Path $stagingPackage -DestinationPath $destination -CompressionLevel Optimal -Force

    if (-not (Test-Path $destination) -or (Get-Item $destination).Length -lt 1024) {
        throw "Build package that bai: $destination"
    }
}

Remove-Item -LiteralPath $stagingRoot -Recurse -Force

Get-ChildItem -LiteralPath $distDir -Filter "*.zip" |
    Select-Object Name, Length, @{Name = "SHA256"; Expression = { (Get-FileHash $_.FullName -Algorithm SHA256).Hash.ToLowerInvariant() } } |
    Format-Table -AutoSize
