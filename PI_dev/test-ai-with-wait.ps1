# PowerShell script to test AI with automatic retry

Write-Host "🤖 Testing OpenAI Integration with Rate Limit Handling" -ForegroundColor Cyan
Write-Host ("=" * 60)

Write-Host "`n⏳ Waiting 60 seconds to avoid rate limits..." -ForegroundColor Yellow
Start-Sleep -Seconds 60

Write-Host "`n✅ Wait complete! Testing AI now...`n" -ForegroundColor Green

php bin/console app:test-ai

if ($LASTEXITCODE -ne 0) {
    Write-Host "`n❌ Test failed. You may still be rate limited." -ForegroundColor Red
    Write-Host "💡 Try visiting http://localhost:8000/goals instead" -ForegroundColor Yellow
} else {
    Write-Host "`n🎉 Success! AI suggestion generated." -ForegroundColor Green
}
