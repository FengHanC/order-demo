$url = "http://order.test/order/create?user_id=1&product_id=1"
$concurrency = 50

$startTime = Get-Date

$jobs = 1..$concurrency | ForEach-Object {
    Start-Job -ScriptBlock {
        param($u)
        try {
            $response = Invoke-WebRequest -Uri $u -TimeoutSec 30 -UseBasicParsing
            return @{
                success = $true
                status  = $response.StatusCode
                content = $response.Content
            }
        } catch {
            return @{
                success = $false
                error   = $_.Exception.Message
            }
        }
    } -ArgumentList $url
}

$results = $jobs | Wait-Job | Receive-Job
$jobs | Remove-Job

$duration = (Get-Date) - $startTime

$success = ($results | Where-Object { $_.success -eq $true }).Count
$failed  = ($results | Where-Object { $_.success -eq $false }).Count

Write-Host "Total: $concurrency"
Write-Host "Success: $success"
Write-Host "Failed: $failed"
Write-Host "Duration: $($duration.TotalSeconds) sec"

$failedSamples = $results | Where-Object { $_.success -eq $false } | Select-Object -First 3
if ($failedSamples) {
    Write-Host ""
    Write-Host "===== Failed Samples ====="
    $failedSamples | ForEach-Object { Write-Host $_.error }
}

$successSamples = $results | Where-Object { $_.success -eq $true } | Select-Object -First 3
if ($successSamples) {
    Write-Host ""
    Write-Host "===== Success Samples ====="
    $successSamples | ForEach-Object { Write-Host $_.content }
}