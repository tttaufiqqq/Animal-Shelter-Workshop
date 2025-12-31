<!DOCTYPE html>
<html>
<head>
    <title>IP Detection Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .result { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #4CAF50; }
        .label { font-weight: bold; color: #555; }
        .value { color: #2196F3; font-family: 'Courier New', monospace; }
        .warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .code { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; margin: 10px 0; }
        .command { color: #66d9ef; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌐 IP Address Detection Test</h1>

        <div class="result">
            <div class="label">PHP Detected IP (request()->ip()):</div>
            <div class="value"><?php echo $_SERVER['REMOTE_ADDR'] ?? 'Not available'; ?></div>
        </div>

        <div class="result">
            <div class="label">SERVER_ADDR:</div>
            <div class="value"><?php echo $_SERVER['SERVER_ADDR'] ?? 'Not available'; ?></div>
        </div>

        <div class="result">
            <div class="label">HTTP_CLIENT_IP:</div>
            <div class="value"><?php echo $_SERVER['HTTP_CLIENT_IP'] ?? 'Not available'; ?></div>
        </div>

        <div class="result">
            <div class="label">HTTP_X_FORWARDED_FOR:</div>
            <div class="value"><?php echo $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'Not available'; ?></div>
        </div>

        <h2>🔍 System IP Detection (Windows ipconfig)</h2>
        <?php
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('ipconfig');
            if ($output) {
                preg_match_all('/IPv4 Address[.\s]*:\s*([0-9.]+)/', $output, $matches);
                if (!empty($matches[1])) {
                    echo '<div class="result success">';
                    echo '<div class="label">All IPv4 Addresses Found:</div>';
                    foreach ($matches[1] as $ip) {
                        $class = ($ip === '127.0.0.1') ? 'warning' : 'value';
                        echo "<div class='$class'>• $ip</div>";
                    }
                    echo '</div>';

                    // Show priority logic
                    $bestIp = null;
                    $priority = 999;
                    foreach ($matches[1] as $ip) {
                        if ($ip === '127.0.0.1') continue;

                        $currentPriority = 999;
                        if (preg_match('/^10\./', $ip)) {
                            $currentPriority = 1;
                            $type = 'Institutional Network (10.x.x.x) - Priority 1';
                        } elseif (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) {
                            $currentPriority = 2;
                            $type = 'Private Network (172.16-31.x.x) - Priority 2';
                        } elseif (!preg_match('/^192\.168\./', $ip)) {
                            $currentPriority = 3;
                            $type = 'Other Network - Priority 3';
                        } elseif (preg_match('/^192\.168\.(56|100)\./', $ip)) {
                            $currentPriority = 999;
                            $type = 'Virtual Adapter (192.168.56/100.x) - SKIPPED';
                        } else {
                            $currentPriority = 4;
                            $type = 'Home Network (192.168.x.x) - Priority 4';
                        }

                        if ($currentPriority < $priority) {
                            $priority = $currentPriority;
                            $bestIp = $ip;
                        }
                    }

                    if ($bestIp) {
                        echo '<div class="result success">';
                        echo '<div class="label">✅ Selected Best IP (used in audit logs):</div>';
                        echo "<div class='value' style='font-size: 1.2em; font-weight: bold;'>$bestIp</div>";
                        echo '</div>';
                    }
                } else {
                    echo '<div class="result warning">No IPv4 addresses found in ipconfig output</div>';
                }
            } else {
                echo '<div class="result warning">⚠️ shell_exec() is disabled or ipconfig command failed</div>';
            }
        } else {
            echo '<div class="result warning">This test is designed for Windows. For Linux/macOS, use: hostname -I</div>';
        }
        ?>

        <h2>📋 Next Steps</h2>
        <div class="result">
            <p><strong>If you see your correct IP above:</strong></p>
            <ol>
                <li>Clear your Laravel logs: <code>storage/logs/laravel.log</code></li>
                <li>Perform a lock/suspend action in the admin panel</li>
                <li>Check the audit log - the IP should now be correct!</li>
                <li>Check Laravel logs for: "Captured real IP address: [your IP]"</li>
            </ol>
        </div>

        <div class="result warning">
            <p><strong>If IP detection is not working (showing 127.0.0.1 only):</strong></p>
            <ol>
                <li>Check if <code>shell_exec()</code> is enabled in your php.ini</li>
                <li>Check if <code>ipconfig</code> command is accessible from PHP</li>
                <li>Look for errors in Laravel logs: <code>storage/logs/laravel.log</code></li>
            </ol>
        </div>

        <div class="code">
            <div class="command"># To check Laravel logs in real-time:</div>
            php artisan pail
        </div>

        <div class="code">
            <div class="command"># Or check the log file directly:</div>
            cat storage/logs/laravel.log | Select-String "real IP"
        </div>
    </div>
</body>
</html>
