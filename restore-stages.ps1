# Source and destination paths
$sourcePath = "C:\Users\trevo\Dropbox\CascadeProjects\PiperPrivacyRecover\PiperPrivacy\piper-privacy\includes\modules\collection-manager\Workflow\Stages"
$destPath = "C:\Users\trevo\Dropbox\CascadeProjects\PiperPrivacy\piper-privacy\includes\modules\collection-manager\workflow\stages"

# Ensure destination directory exists
if (-not (Test-Path $destPath)) {
    New-Item -ItemType Directory -Path $destPath -Force
}

# File mapping (old name -> new name)
$fileMapping = @{
    "BaseStage.php" = "class-base-stage.php"
    "DraftStage.php" = "class-draft-stage.php"
    "ImplementationStage.php" = "class-implementation-stage.php"
    "PIAInProgressStage.php" = "class-pia-in-progress-stage.php"
    "PIARequiredStage.php" = "class-pia-required-stage.php"
    "PIAReviewStage.php" = "class-pia-review-stage.php"
    "PTAInProgressStage.php" = "class-pta-in-progress-stage.php"
    "PTARequiredStage.php" = "class-pta-required-stage.php"
    "PTAReviewStage.php" = "class-pta-review-stage.php"
    "RetirementStage.php" = "class-retirement-stage.php"
}

# Copy and rename each file
foreach ($oldName in $fileMapping.Keys) {
    $sourcefile = Join-Path $sourcePath $oldName
    $destFile = Join-Path $destPath $fileMapping[$oldName]
    
    Write-Host "Copying $oldName to $($fileMapping[$oldName])"
    Copy-Item -Path $sourcefile -Destination $destFile -Force
}
