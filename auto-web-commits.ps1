# Script to automate 39 commits specifically for website content updates
# Make sure Git is installed and you're already logged in (git credentials are stored)

# Change to the repository directory
Set-Location -Path $PSScriptRoot

# Product names and descriptions for randomization
$products = @(
    @{ Name = "Deglet Nour"; Description = "Sweet and delicate flavor with a soft texture"; },
    @{ Name = "Medjool"; Description = "Rich caramel flavor with a soft, chewy texture"; },
    @{ Name = "Sukkary"; Description = "Distinctively sweet taste with hints of vanilla"; },
    @{ Name = "Barhi"; Description = "Soft and delicate with honey undertones"; },
    @{ Name = "Halawi"; Description = "Sweet taste with a caramel-like texture"; },
    @{ Name = "Khudri"; Description = "Rich flavor with a firm, meaty texture"; },
    @{ Name = "Luxury Gift Box"; Description = "An assortment of our finest date varieties"; },
    @{ Name = "Organic Date Syrup"; Description = "Natural sweetener made from our premium dates"; }
)

# Function to make a small change to the home page and commit
function Make-WebsiteCommit {
    param (
        [int]$commitNumber
    )

    # Make sure the path is correct regardless of where the script is run from
    $homePagePath = Join-Path -Path $PSScriptRoot -ChildPath "templates\home\index.html.twig"
    $content = Get-Content -Path $homePagePath -Raw

    # Choose a random modification type
    $changeType = Get-Random -Minimum 1 -Maximum 5

    switch ($changeType) {
        1 {
            # Modify a product price
            $randomProduct = $products | Get-Random
            $newPrice = Get-Random -Minimum 8 -Maximum 30
            $priceFormat = "$" + $newPrice + "." + (Get-Random -Minimum 49 -Maximum 99) + " / 500g"
            
            # Find and replace a price for the product
            $pattern = "<h3 class=""product-title"">$($randomProduct.Name)</h3>[\s\S]*?<p class=""product-price"">.*?<\/p>"
            $replacement = "<h3 class=""product-title"">$($randomProduct.Name)</h3>$([Environment]::NewLine)                            <div class=""product-rating"">$([Environment]::NewLine)                                <i class=""fas fa-star""></i>$([Environment]::NewLine)                                <i class=""fas fa-star""></i>$([Environment]::NewLine)                                <i class=""fas fa-star""></i>$([Environment]::NewLine)                                <i class=""fas fa-star""></i>$([Environment]::NewLine)                                <i class=""fas fa-star""></i>$([Environment]::NewLine)                            </div>$([Environment]::NewLine)                            <p class=""product-price"">$priceFormat</p>"
            
            $content = $content -replace $pattern, $replacement
            $content | Set-Content -Path $homePagePath
              git add $homePagePath
            git commit -m "Update ${commitNumber} - Updated price for $($randomProduct.Name) to $priceFormat"
        }
        2 {
            # Add a comment to explain some part of the template
            $randomLine = Get-Random -Minimum 400 -Maximum 900
            $contents = Get-Content -Path $homePagePath
            $contents[$randomLine] = $contents[$randomLine] + "{# Enhanced for better user experience - Update $commitNumber #}"
            $contents | Set-Content -Path $homePagePath
              git add $homePagePath
            git commit -m "Update ${commitNumber} - Added clarifying comment to home page template"
        }
        3 {
            # Modify meta description or title
            if ($content -match "{% block title %}(.*?){% endblock %}") {
                $titleVariations = @(
                    "Premium Tunisian Dates | Nefta Date Plates",
                    "Exquisite Date Selection | Nefta Organic Dates",
                    "Finest Tunisian Dates | Nefta Premium Selections",
                    "Organic Date Varieties | Nefta Date Collection"
                )
                $newTitle = $titleVariations | Get-Random
                $content = $content -replace "{% block title %}.*?{% endblock %}", "{% block title %}$newTitle{% endblock %}"
                $content | Set-Content -Path $homePagePath
                  git add $homePagePath
                git commit -m "Update ${commitNumber} - Improved SEO title to '$newTitle'"
            }
        }
        4 {
            # Update a feature description
            $featureTexts = @(
                "Our dates are grown using only natural farming methods that respect the environment and ensure the highest quality.",
                "We use traditional farming practices passed down through generations, ensuring sustainable production.",
                "Our organic farming methods preserve the natural ecosystem while producing the finest quality dates.",
                "We grow our dates without synthetic pesticides, focusing on environmental sustainability."
            )
            
            $newFeatureText = $featureTexts | Get-Random
            $pattern = "<h3>Organic Farming</h3>\s*<p>.*?</p>"
            $replacement = "<h3>Organic Farming</h3>$([Environment]::NewLine)                    <p>$newFeatureText</p>"
            
            $content = $content -replace $pattern, $replacement
            $content | Set-Content -Path $homePagePath
              git add $homePagePath
            git commit -m "Update ${commitNumber} - Enhanced organic farming description"
        }
    }

    # Push after each commit
    git push origin master
    
    # Wait a bit between commits
    Start-Sleep -Seconds 2
}

# Make the specified number of commits
for ($i = 1; $i -le 39; $i++) {
    Write-Host "Creating website commit $i of 39..."
    Make-WebsiteCommit -commitNumber $i
    
    # Progress feedback
    $percentComplete = ($i / 39) * 100
    Write-Progress -Activity "Creating website commits" -Status "Progress:" -PercentComplete $percentComplete
    
    # Random delay between commits (3-7 seconds)
    $delay = Get-Random -Minimum 3 -Maximum 7
    Start-Sleep -Seconds $delay
}

Write-Host "Completed 39 commits to update the website content."
