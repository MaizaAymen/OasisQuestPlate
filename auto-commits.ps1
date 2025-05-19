# Script to automate 39 commits to GitHub
# Make sure Git is installed and you're already logged in (git credentials are stored)

# Change to the repository directory
Set-Location -Path $PSScriptRoot

# Check if git is initialized
if (-not (Test-Path -Path ".git")) {
    Write-Host "Git repository not found. Initializing git repository..."
    git init
    git add .
    git commit -m "Initial commit"
}

# Ensure there's a remote repository
$remoteExists = git remote -v
if (-not $remoteExists) {
    $remoteUrl = Read-Host -Prompt "Enter your GitHub repository URL (e.g., https://github.com/username/repo.git)"
    git remote add origin $remoteUrl
}

# Create a README.md file if it doesn't exist
if (-not (Test-Path -Path "README.md")) {
    "# OasisQuestPlate - Premium Tunisian Date Store" | Out-File -FilePath "README.md"
    git add "README.md"
    git commit -m "Add README file"
    git push -u origin master
}

# Function to make a small change and commit
function Make-CommitChange {
    param (
        [int]$commitNumber
    )

    # Choose a random type of change
    $changeTypes = @("readme", "comment", "documentation")
    $changeType = $changeTypes | Get-Random

    switch ($changeType) {
        "readme" {
            # Append a line to README.md
            "## Update $commitNumber - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" | Add-Content -Path "README.md"
            "* Made some improvements to the project" | Add-Content -Path "README.md"            git add "README.md"
            git commit -m "Update ${commitNumber} - Enhanced documentation"
        }
        "comment" {
            # Add a comment to a random Twig file
            $twigFiles = Get-ChildItem -Path "templates" -Recurse -Include "*.twig"
            $randomFile = $twigFiles | Get-Random
            "{# Update $commitNumber - Improved template structure on $(Get-Date -Format 'yyyy-MM-dd') #}" | Add-Content -Path $randomFile.FullName            git add $randomFile.FullName
            git commit -m "Update ${commitNumber} - Add clarifying comment to $($randomFile.Name)"
        }
        "documentation" {
            # Update a PHP file's documentation
            $phpFiles = Get-ChildItem -Path "src" -Recurse -Include "*.php"
            $randomFile = $phpFiles | Get-Random
            
            $content = Get-Content -Path $randomFile.FullName
            # Add a comment near the top of the file (after the opening <?php tag)
            $newContent = @()
            $docAdded = $false
            
            foreach ($line in $content) {
                $newContent += $line
                if ($line -match "<?php" -and -not $docAdded) {
                    $newContent += ""
                    $newContent += "// Update $commitNumber - $(Get-Date -Format 'yyyy-MM-dd'): Enhanced documentation"
                    $docAdded = $true
                }
            }
            
            $newContent | Set-Content -Path $randomFile.FullName            git add $randomFile.FullName
            git commit -m "Update ${commitNumber} - Improved documentation in $($randomFile.Name)"
        }
    }

    # Push after each commit
    git push origin master
    
    # Wait a bit between commits
    Start-Sleep -Seconds 2
}

# Make the specified number of commits
for ($i = 1; $i -le 39; $i++) {
    Write-Host "Creating commit $i of 39..."
    Make-CommitChange -commitNumber $i
    
    # Progress feedback
    $percentComplete = ($i / 39) * 100
    Write-Progress -Activity "Creating commits" -Status "Progress:" -PercentComplete $percentComplete
    
    # Random delay between commits (3-10 seconds)
    $delay = Get-Random -Minimum 3 -Maximum 10
    Start-Sleep -Seconds $delay
}

Write-Host "Completed 39 commits to the repository."
