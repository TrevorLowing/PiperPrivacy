$projectRoot = "C:\Users\trevo\Dropbox\CascadeProjects\PiperPrivacy\piper-privacy"

# Function to convert PascalCase to kebab-case
function Convert-ToKebabCase {
    param([string]$text)
    if ($text -match '^class-') {
        return $text
    }
    $kebab = $text -replace '([A-Z])', '-$1'
    if ($kebab.StartsWith('-')) {
        $kebab = $kebab.Substring(1)
    }
    return $kebab.ToLower()
}

# Function to check if a file should be prefixed with class-
function Should-AddClassPrefix {
    param([string]$fileName)
    if ($fileName -match '^class-' -or $fileName -match '^index\.php$' -or $fileName -match '\.(js|css|txt|md|json)$') {
        return $false
    }
    return $true
}

# Recursively process directories and files
function Process-Directory {
    param([string]$path)
    
    Get-ChildItem $path -Recurse | ForEach-Object {
        $relativePath = $_.FullName.Substring($projectRoot.Length + 1)
        
        if ($_.PSIsContainer) {
            # Directory
            $newName = Convert-ToKebabCase $_.Name
            if ($newName -ne $_.Name) {
                Write-Host "Directory needs renaming: $relativePath -> $newName"
            }
        } else {
            # File
            $baseName = [System.IO.Path]::GetFileNameWithoutExtension($_.Name)
            $ext = [System.IO.Path]::GetExtension($_.Name)
            
            if ((Should-AddClassPrefix $_.Name) -and ($ext -eq '.php')) {
                $newBaseName = Convert-ToKebabCase $baseName
                if (-not $newBaseName.StartsWith('class-')) {
                    $newBaseName = "class-$newBaseName"
                }
                $newName = "$newBaseName$ext"
                if ($newName -ne $_.Name) {
                    Write-Host "File needs renaming: $relativePath -> $newName"
                }
            }
        }
    }
}

Process-Directory $projectRoot
