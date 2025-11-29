# danish-tunnels-persistent.ps1

Write-Host "Starting persistent SSH tunnels with auto-restart for Danish..."

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
Start-Tunnel -Port 3307 -RemotePort 3306 -User "eilya" -Host "device-mysql-1-ip" -Description "Eilya MySQL"
Start-Tunnel -Port 3308 -RemotePort 3306 -User "atiqah" -Host "device-mysql-2-ip" -Description "Atiqah MySQL"
Start-Tunnel -Port 3309 -RemotePort 3306 -User "piqa" -Host "device-mysql-3-ip" -Description "Piqa MySQL"
Start-Tunnel -Port 5433 -RemotePort 5432 -User "taufiq" -Host "device-postgresql-ip" -Description "Taufiq PostgreSQL"

Write-Host "`nAll 4 tunnels started with auto-restart enabled for Danish!"
Write-Host "To stop all tunnels, close this PowerShell window or run: Get-Job | Stop-Job"

# Keep script running
while ($true) {
    Start-Sleep -Seconds 60
    $jobs = Get-Job
    Write-Host "`n[$(Get-Date)] Status: $($jobs.Count) tunnel jobs running"
}