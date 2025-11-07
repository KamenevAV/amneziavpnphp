<?php
/**
 * Test QR code generation with real config
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/inc/QrUtil.php';

// Sample AmneziaWG config
$config = <<<EOT
[Interface]
PrivateKey = YBtGKhj6pCKHBh8GtTaLV9WqVDCK4EWqIwXqP3vCg1M=
Address = 10.8.1.2/32
DNS = 1.1.1.1, 1.0.0.1
Jc = 3
Jmin = 40
Jmax = 70
S1 = 86
S2 = 3
H1 = 1
H2 = 2
H3 = 3
H4 = 4

[Peer]
PublicKey = ZVfqrLBQs3MpZjGMqOmDk0nIVMmVqRWnZHQqbOoJwXk=
PresharedKey = uOPELRGBqTqnJPqEGCPRSLVjMW3RGFOQOhMDqmWLBBo=
Endpoint = 123.45.67.89:51820
AllowedIPs = 0.0.0.0/0, ::/0
PersistentKeepalive = 25
EOT;

try {
    echo "Testing QR code generation...\n\n";
    
    // Generate payload
    $payloadOld = QrUtil::encodeOldPayloadFromConf($config);
    echo "Payload (URL-safe base64): " . substr($payloadOld, 0, 100) . "...\n\n";
    
    // Generate QR code PNG
    $dataUri = QrUtil::pngBase64($payloadOld);
    echo "QR Code Data URI: " . substr($dataUri, 0, 100) . "...\n\n";
    
    // Save to file for inspection
    if (preg_match('/^data:image\/(png|svg\+xml);base64,(.+)$/', $dataUri, $m)) {
        $imageData = base64_decode($m[2]);
        $ext = $m[1] === 'svg+xml' ? 'svg' : 'png';
        $filename = __DIR__ . "/test_qr." . $ext;
        file_put_contents($filename, $imageData);
        echo "✅ QR code saved to: $filename\n";
    }
    
    echo "\n✅ Success! QR code generation working correctly.\n";
    
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
