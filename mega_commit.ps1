# mega_commit.ps1
# Script to perform individual commits for each modified/new file.

$changes = git status -s

foreach ($line in $changes) {
    # Extract the file path (ignoring the status characters at the start)
    $file = $line.Substring(3).Trim()
    
    if ($file) {
        Write-Host "Committing file: $file" -ForegroundColor Cyan
        
        # Determine a simple prefix based on the file path
        $prefix = "feat"
        if ($file -match "frontend") { $prefix = "feat(ui)" }
        elseif ($file -match "backend") { $prefix = "feat(api)" }
        elseif ($file -match "migration") { $prefix = "chore(db)" }
        
        git add "$file"
        git commit -m "$prefix: updated $file"
    }
}

Write-Host "`nDone! All files have been committed individually." -ForegroundColor Green
