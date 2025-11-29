#!/bin/bash
# shafiqah-tunnels-persistent.sh

echo "Starting persistent SSH tunnels with auto-restart for Shafiqah..."

# Function to start and monitor a tunnel
start_tunnel() {
    local port=$1
    local remote_port=$2
    local user=$3
    local host=$4
    local description=$5
    
    while true; do
        echo "[$(date)] [$description] Starting tunnel on port $port..."
        
        ssh -o ServerAliveInterval=30 \
            -o ServerAliveCountMax=5 \
            -o TCPKeepAlive=yes \
            -o ExitOnForwardFailure=yes \
            -o ConnectTimeout=10 \
            -L ${port}:127.0.0.1:${remote_port} \
            ${user}@${host} \
            -N
        
        echo "[$(date)] [$description] Tunnel died, restarting in 5 seconds..."
        sleep 5
    done
}

# Start all tunnels in background
start_tunnel 3307 3306 "eilya" "device-mysql-1-ip" "Eilya MySQL" &
start_tunnel 3308 3306 "atiqah" "device-mysql-2-ip" "Atiqah MySQL" &
start_tunnel 1434 1433 "laptop-4k8hhere\\user" "device-sqlserver-ip" "SQL Server" &
start_tunnel 5433 5432 "taufiq" "device-postgresql-ip" "Taufiq PostgreSQL" &

echo ""
echo "All 4 tunnels started with auto-restart enabled for Shafiqah!"
echo "To stop all tunnels, press Ctrl+C or run: pkill -f 'ssh.*-L'"
echo ""

# Keep script running and show status
while true; do
    sleep 60
    tunnel_count=$(pgrep -f "ssh.*-L" | wc -l)
    echo "[$(date)] Status: $tunnel_count tunnel processes running"
done
