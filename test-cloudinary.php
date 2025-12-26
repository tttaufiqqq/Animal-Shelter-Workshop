<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Fix corrupted php.ini curl.cainfo setting
ini_set('curl.cainfo', __DIR__ . '/storage/cacert.pem');
ini_set('openssl.cafile', __DIR__ . '/storage/cacert.pem');

try {
    echo "Testing Cloudinary upload...\n";

    $testFile = storage_path('app/public/reports/cat1.jpg');

    if (!file_exists($testFile)) {
        echo "ERROR: Test file not found: $testFile\n";
        exit(1);
    }

    echo "Uploading $testFile to Cloudinary...\n";

    $result = cloudinary()->uploadApi()->upload($testFile, [
        'folder' => 'test',
        'public_id' => 'test-upload',
    ]);

    echo "SUCCESS!\n";
    echo "Secure URL: " . $result['secure_url'] . "\n";
    echo "Public ID: " . $result['public_id'] . "\n";

    // Test URL generation
    $url = cloudinary()->image($result['public_id'])->toUrl();
    echo "URL from image()->toUrl(): $url\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
