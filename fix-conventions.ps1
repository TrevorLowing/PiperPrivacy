# Rename directories
$dirs = @(
    @{
        Old = "includes/modules/collection-manager/Admin"
        New = "includes/modules/collection-manager/admin-temp"
    },
    @{
        Old = "includes/modules/collection-manager/Forms"
        New = "includes/modules/collection-manager/forms-temp"
    },
    @{
        Old = "includes/modules/collection-manager/PostTypes"
        New = "includes/modules/collection-manager/post-types-temp"
    }
)

# Rename files
$files = @(
    @{
        Old = "includes/modules/collection-manager/CollectionManager.php"
        New = "includes/modules/collection-manager/class-collection-manager.php"
    }
)

# Working directory
$workingDir = "C:\Users\trevo\Dropbox\CascadeProjects\PiperPrivacy\piper-privacy"
Set-Location $workingDir

# Rename directories
foreach ($dir in $dirs) {
    if (Test-Path $dir.Old) {
        Write-Host "Renaming directory: $($dir.Old) -> $($dir.New)"
        Move-Item -Path $dir.Old -Destination $dir.New -Force
        Move-Item -Path "$($dir.New)" -Destination ($dir.New -replace "-temp","") -Force
    }
}

# Rename files
foreach ($file in $files) {
    if (Test-Path $file.Old) {
        Write-Host "Renaming file: $($file.Old) -> $($file.New)"
        Move-Item -Path $file.Old -Destination $file.New -Force
    }
}
