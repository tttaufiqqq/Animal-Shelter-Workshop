<?php

/**
 * Download CA Bundle for Windows cURL/SSL issues
 * Run with: php fix-ssl.php
 */

echo "Downloading CA Bundle for Windows cURL...\n";

$caBundleUrl = 'https://curl.se/ca/cacert.pem';
$caBundlePath = __DIR__ . '/storage/cacert.pem';

echo "Downloading from: $caBundleUrl\n";
echo "Saving to: $caBundlePath\n\n";

$content = file_get_contents($caBundleUrl);

if ($content === false) {
    echo "ERROR: Failed to download CA bundle\n";
    echo "Please download manually from: $caBundleUrl\n";
    echo "And save it to: $caBundlePath\n";
    exit(1);
}

file_put_contents($caBundlePath, $content);

echo "SUCCESS!\n\n";
echo "CA Bundle downloaded successfully to: $caBundlePath\n\n";
echo "Now add this to your .env file:\n";
echo "CURL_CA_BUNDLE=" . $caBundlePath . "\n\n";
echo "Then restart your server.\n";
