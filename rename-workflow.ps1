# Working directory
$workingDir = "C:\Users\trevo\Dropbox\CascadeProjects\PiperPrivacy\piper-privacy\includes\modules\collection-manager\workflow"

# File mapping (old name -> new name)
$fileMapping = @{
    "BaseWorkflow.php" = "class-base-workflow.php"
    "CollectionWorkflow.php" = "class-collection-workflow.php"
    "NotificationManager.php" = "class-notification-manager.php"
    "WorkflowTracker.php" = "class-workflow-tracker.php"
}

# Rename each file
foreach ($oldName in $fileMapping.Keys) {
    $oldPath = Join-Path $workingDir $oldName
    $newPath = Join-Path $workingDir $fileMapping[$oldName]
    
    if (Test-Path $oldPath) {
        Write-Host "Renaming $oldName to $($fileMapping[$oldName])"
        Move-Item -Path $oldPath -Destination $newPath -Force
    }
}
