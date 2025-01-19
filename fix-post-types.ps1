# Working directory
$workingDir = "C:\Users\trevo\Dropbox\CascadeProjects\PiperPrivacy\piper-privacy\includes\post-types"

# File mapping (old name -> new name)
$fileMapping = @{
    "class-privacy_collection.php" = "class-privacy-collection.php"
    "class-privacy_impact.php" = "class-privacy-impact.php"
    "class-privacy_threshold.php" = "class-privacy-threshold.php"
}

# Change to working directory
Set-Location $workingDir

# Rename files
foreach ($oldName in $fileMapping.Keys) {
    $oldPath = Join-Path $workingDir $oldName
    $newPath = Join-Path $workingDir $fileMapping[$oldName]
    
    if (Test-Path $oldPath) {
        Write-Host "Renaming $oldName to $($fileMapping[$oldName])"
        Move-Item -Path $oldPath -Destination $newPath -Force
    }
}
