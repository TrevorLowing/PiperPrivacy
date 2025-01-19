$standalone = "privacy-collection-manager"
$integrated = "piper-privacy/includes/modules/collection-manager"

Write-Host "Comparing Privacy Collection Manager implementations:`n"
Write-Host "1. Standalone: $standalone"
Write-Host "2. Integrated: $integrated`n"
Write-Host "=== Directory Structure Comparison ===`n"

$standalone_files = Get-ChildItem -Path $standalone -Recurse -File
$integrated_files = Get-ChildItem -Path $integrated -Recurse -File

Write-Host "Files unique to standalone version:"
foreach ($file in $standalone_files) {
    $relativePath = $file.FullName.Substring($standalone.Length + 1)
    $integratedPath = Join-Path $integrated $relativePath
    if (-not (Test-Path $integratedPath)) {
        Write-Host "  + $relativePath"
    }
}

Write-Host "`nFiles unique to integrated version:"
foreach ($file in $integrated_files) {
    $relativePath = $file.FullName.Substring($integrated.Length + 1)
    $standalonePath = Join-Path $standalone $relativePath
    if (-not (Test-Path $standalonePath)) {
        Write-Host "  + $relativePath"
    }
}

Write-Host "`n=== Content Comparison of Common Files ===`n"
foreach ($file in $standalone_files) {
    $relativePath = $file.FullName.Substring($standalone.Length + 1)
    $integratedPath = Join-Path $integrated $relativePath
    if (Test-Path $integratedPath) {
        $diff = Compare-Object -ReferenceObject (Get-Content $file.FullName) -DifferenceObject (Get-Content $integratedPath)
        if ($diff) {
            Write-Host "Differences found in: $relativePath"
            $diff | ForEach-Object {
                if ($_.SideIndicator -eq "<=") {
                    Write-Host "  [Standalone] $($_.InputObject)"
                } else {
                    Write-Host "  [Integrated] $($_.InputObject)"
                }
            }
            Write-Host ""
        }
    }
}
