# Working directory
$workingDir = "C:\Users\trevo\Dropbox\CascadeProjects\PiperPrivacy\piper-privacy"

# Directory renames needed
$dirRenames = @(
    @{
        Old = "includes\Core"
        New = "includes\core-temp"
        Final = "includes\core"
    },
    @{
        Old = "includes\UI"
        New = "includes\ui-temp"
        Final = "includes\ui"
    }
)

# Change to working directory
Set-Location $workingDir

# Rename directories (two-step process to handle case sensitivity)
foreach ($dir in $dirRenames) {
    $oldPath = Join-Path $workingDir $dir.Old
    $tempPath = Join-Path $workingDir $dir.New
    $finalPath = Join-Path $workingDir $dir.Final
    
    if (Test-Path $oldPath) {
        Write-Host "Renaming $($dir.Old) to $($dir.Final)"
        Move-Item -Path $oldPath -Destination $tempPath -Force
        Move-Item -Path $tempPath -Destination $finalPath -Force
    }
}

# Now let's check PHP files in these directories to ensure they follow class- prefix convention
function Rename-PhpFiles {
    param([string]$directory)
    
    Get-ChildItem $directory -Filter "*.php" -Recurse | ForEach-Object {
        if ($_.Name -notmatch "^class-" -and $_.Name -ne "index.php") {
            $newName = "class-" + ($_.Name -replace "([A-Z])", "-$1" -replace "^-", "").ToLower()
            $newPath = Join-Path $_.Directory.FullName $newName
            Write-Host "Renaming $($_.Name) to $newName"
            Move-Item -Path $_.FullName -Destination $newPath -Force
        }
    }
}

# Check PHP files in core and ui directories
$dirsToCheck = @(
    "includes\core",
    "includes\ui",
    "includes\modules"
)

foreach ($dir in $dirsToCheck) {
    $dirPath = Join-Path $workingDir $dir
    if (Test-Path $dirPath) {
        Write-Host "Checking files in $dir"
        Rename-PhpFiles $dirPath
    }
}
