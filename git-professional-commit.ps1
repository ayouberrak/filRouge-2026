# Dynamically get modified and untracked files
$files = git status --short | ForEach-Object { $_.Substring(3) }

foreach ($file in $files) {
    if (Test-Path $file) {
        # Determine prefix and message based on file path
        $parts = $file -split '/'
        $prefix = "chore"
        $topic = "system"
        
        if ($file -like "*database/migrations*") { $prefix = "feat"; $topic = "migration" }
        elseif ($file -like "*Modules/Activity*") { $prefix = "feat"; $topic = "activity" }
        elseif ($file -like "*Modules/Brief*") { $prefix = "refactor"; $topic = "brief" }
        elseif ($file -like "*Modules/Livrable*") { $prefix = "feat"; $topic = "livrable" }
        elseif ($file -like "*routes/api.php") { $prefix = "feat"; $topic = "api" }
        
        $fileName = $parts[-1]
        $message = "$prefix($topic): update $fileName"
        
        git add $file
        git commit -m $message
        Write-Host "Committed: $file with message: $message" -ForegroundColor Green
    }
}

# Cleanup
if (Test-Path "ttt.txt") { Remove-Item "ttt.txt" }
# git push # Optional: let user push

