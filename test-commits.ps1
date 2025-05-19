# This is a test script with only 2 commits to verify the fix

# Change to the repository directory
Set-Location -Path $PSScriptRoot

# Product names and descriptions for randomization
$products = @(
    @{ Name = "Deglet Nour"; Description = "Sweet and delicate flavor with a soft texture"; },
    @{ Name = "Medjool"; Description = "Rich caramel flavor with a soft, chewy texture"; }
)

# Function to make a small change to the home page and commit
function Make-WebsiteCommit {
    param (
        [int]$commitNumber
    )

    # Make sure the path is correct regardless of where the script is run from
    $homePagePath = Join-Path -Path $PSScriptRoot -ChildPath "templates\home\index.html.twig"
    $content = Get-Content -Path $homePagePath -Raw

    # Add a comment to explain some part of the template
    $randomLine = Get-Random -Minimum 400 -Maximum 900
    $contents = Get-Content -Path $homePagePath
    $contents[$randomLine] = $contents[$randomLine] + "{# Test commit $commitNumber #}"
    $contents | Set-Content -Path $homePagePath
    git add $homePagePath
    git commit -m "Test ${commitNumber} - Added test comment to verify branch fix"

    # Push after each commit
    git push origin main
    
    # Wait a bit between commits
    Start-Sleep -Seconds 2
}

# Make only 2 test commits
for ($i = 1; $i -le 2; $i++) {
    Write-Host "Creating test commit $i of 2..."
    Make-WebsiteCommit -commitNumber $i
}

Write-Host "Test commits completed. Check if they were pushed successfully to the main branch."
