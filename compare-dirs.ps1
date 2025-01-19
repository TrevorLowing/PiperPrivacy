$dir1 = "piper-privacy"
$dir2 = "temp-piper-privacy"

$files1 = Get-ChildItem -Path $dir1 -Recurse -File
$files2 = Get-ChildItem -Path $dir2 -Recurse -File

foreach ($file1 in $files1) {
    $relativePath = $file1.FullName.Substring($dir1.Length + 1)
    $file2Path = Join-Path $dir2 $relativePath
    
    if (Test-Path $file2Path) {
        $diff = Compare-Object -ReferenceObject (Get-Content $file1.FullName) -DifferenceObject (Get-Content $file2Path)
        if ($diff) {
            Write-Host "Differences found in: $relativePath"
            $diff | ForEach-Object {
                if ($_.SideIndicator -eq "<=") {
                    Write-Host "In ${dir1}: $($_.InputObject)"
                } else {
                    Write-Host "In ${dir2}: $($_.InputObject)"
                }
            }
            Write-Host "-------------------"
        }
    } else {
        Write-Host "File only exists in ${dir1}: $relativePath"
    }
}

foreach ($file2 in $files2) {
    $relativePath = $file2.FullName.Substring($dir2.Length + 1)
    $file1Path = Join-Path $dir1 $relativePath
    
    if (-not (Test-Path $file1Path)) {
        Write-Host "File only exists in ${dir2}: $relativePath"
    }
}
