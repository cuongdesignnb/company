param(
    [string]$ProjectDir = ""
)

$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

if (-not $ProjectDir) {
    $ProjectDir = Split-Path -Parent $PSScriptRoot
}

Set-Location $ProjectDir
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

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

$requiredEntries = @{
    "cb-company-theme" = @(
        "style.css",
        "functions.php",
        "inc/setup.php",
        "inc/enqueue.php",
        "inc/helpers.php",
        "assets/css/main.css"
    )
    "cb-company-core" = @(
        "cb-company-core.php",
        "includes/admin/rest.php",
        "includes/admin/frontend-edit.php",
        "assets/frontend-edit/frontend-edit.css",
        "assets/frontend-edit/frontend-edit.js"
    )
    "cb-webp-converter" = @("cb-webp-converter.php")
    "cb-site-transfer" = @("cb-site-transfer.php")
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
    if (Test-Path $destination) {
        Remove-Item -LiteralPath $destination -Force
    }

    $archive = [System.IO.Compression.ZipFile]::Open(
        $destination,
        [System.IO.Compression.ZipArchiveMode]::Create
    )
    try {
        Get-ChildItem -LiteralPath $stagingPackage -File -Recurse -Force | ForEach-Object {
            $relative = $_.FullName.Substring($stagingPackage.Length).TrimStart([char[]]"\/")
            $entryName = $package.Key + "/" + ($relative -replace "\\", "/")
            [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                $archive,
                $_.FullName,
                $entryName,
                [System.IO.Compression.CompressionLevel]::Optimal
            ) | Out-Null
        }
    }
    finally {
        $archive.Dispose()
    }

    if (-not (Test-Path $destination) -or (Get-Item $destination).Length -lt 1024) {
        throw "Build package that bai: $destination"
    }

    $verificationArchive = [System.IO.Compression.ZipFile]::OpenRead($destination)
    try {
        $entryNames = @($verificationArchive.Entries | ForEach-Object { $_.FullName })
        foreach ($requiredRelativePath in $requiredEntries[$package.Key]) {
            $requiredEntry = $package.Key + "/" + $requiredRelativePath
            if ($entryNames -notcontains $requiredEntry) {
                throw "Package thieu file bat buoc: $requiredEntry"
            }
        }
        if ($entryNames | Where-Object { $_.Contains("\") }) {
            throw "Package chua ZIP entry dung dau gach nguoc Windows: $destination"
        }
        if ($entryNames | Where-Object { $_.StartsWith("/", [System.StringComparison]::Ordinal) -or $_.Contains("../") }) {
            throw "Package chua ZIP entry khong an toan: $destination"
        }
        if ($entryNames | Where-Object { -not $_.StartsWith($package.Key + "/", [System.StringComparison]::Ordinal) }) {
            throw "Package co file nam ngoai thu muc goc: $destination"
        }
        if ($entryNames | Where-Object { $_.StartsWith($package.Key + "/" + $package.Key + "/", [System.StringComparison]::Ordinal) }) {
            throw "Package bi long hai thu muc trung ten: $destination"
        }
    }
    finally {
        $verificationArchive.Dispose()
    }

    $verificationRoot = Join-Path $stagingRoot ("verify-" + $package.Key)
    New-Item -ItemType Directory -Force -Path $verificationRoot | Out-Null
    [System.IO.Compression.ZipFile]::ExtractToDirectory($destination, $verificationRoot)

    Get-ChildItem -LiteralPath $stagingPackage -File -Recurse -Force | ForEach-Object {
        $relative = $_.FullName.Substring($stagingPackage.Length).TrimStart([char[]]"\/")
        $extractedFile = Join-Path (Join-Path $verificationRoot $package.Key) $relative
        if (-not (Test-Path -LiteralPath $extractedFile -PathType Leaf)) {
            throw "Package giai nen thieu file: $($package.Key)/$($relative -replace '\\', '/')"
        }

        $sourceHash = (Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash
        $extractedHash = (Get-FileHash -LiteralPath $extractedFile -Algorithm SHA256).Hash
        if ($sourceHash -ne $extractedHash) {
            throw "Package giai nen sai noi dung: $($package.Key)/$($relative -replace '\\', '/')"
        }
    }

    Remove-Item -LiteralPath $verificationRoot -Recurse -Force
}

Remove-Item -LiteralPath $stagingRoot -Recurse -Force

Get-ChildItem -LiteralPath $distDir -Filter "*.zip" |
    Select-Object Name, Length, @{Name = "SHA256"; Expression = { (Get-FileHash $_.FullName -Algorithm SHA256).Hash.ToLowerInvariant() } } |
    Format-Table -AutoSize
