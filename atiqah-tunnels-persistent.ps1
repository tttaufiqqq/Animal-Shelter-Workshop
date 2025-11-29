# atiqah-tunnels-persistent.ps1

Write-Host "Starting persistent SSH tunnels with auto-restart for Atiqah..."

# Function to start a tunnel
function Start-Tunnel {
    param($Port, $RemotePort, $User, $Host, $Description)

    Start-Job -ScriptBlock {
        param($p, $rp, $u, $h, $desc)

        while ($true) {
            Write-Host "[$desc] Starting tunnel on port $p..."

            $process = Start-Process ssh -ArgumentList "-o ServerAliveInterval=30 -o ServerAliveCountMax=5 -o TCPKeepAlive=yes -o ExitOnForwardFailure=yes -L ${p}:127.0.0.1:${rp} ${u}@${h} -N" -PassThru -NoNewWindow

            # Wait for process to exit
            $process.WaitForExit()

            Write-Host "[$desc] Tunnel died, restarting in 5 seconds..."
            Start-Sleep -Seconds 5
        }
    } -ArgumentList $Port, $RemotePort, $User, $Host, $Description
}

# Start all tunnels
Start-Tunnel -Port 3307 -RemotePort 3306 -User "eilya" -Host "172.20.10.2" -Description "Eilya MySQL"
Start-Tunnel -Port 3309 -RemotePort 3306 -User "piqa" -Host "172.20.10.12" -Description "Piqa MySQL"
Start-Tunnel -Port 1434 -RemotePort 1433 -User "laptop-4k8hhere\user" -Host "172.20.10.13" -Description "SQL Server"
Start-Tunnel -Port 5433 -RemotePort 5432 -User "taufiq" -Host "172.20.10.10" -Description "Taufiq PostgreSQL"

Write-Host "`nAll 4 tunnels started with auto-restart enabled for Atiqah!"
Write-Host "To stop all tunnels, close this PowerShell window or run: Get-Job | Stop-Job"

# Keep script running
while ($true) {
    Start-Sleep -Seconds 60
    $jobs = Get-Job
    Write-Host "`n[$(Get-Date)] Status: $($jobs.Count) tunnel jobs running"
}
