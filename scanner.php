<?php
// scanner.php
function scan_dir($dir)
{
    $results = [];
    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $path = "$dir/$file";
        if (is_dir($path)) {
            $results = array_merge($results, scan_dir($path));
        } elseif (preg_match('/\.(php|js)$/', $file)) {
            $contents = file_get_contents($path);

            if (
                preg_match('/eval\(/i', $contents) ||
                preg_match('/base64_decode/i', $contents) ||
                preg_match('/gzinflate|gzuncompress|str_rot13/i', $contents) ||
                preg_match('/window\.location\.href/i', $contents) ||
                preg_match('/zone-xsec\.com/i', $contents)
            ) {
                $results[] = $path;
            }
        }
    }
    return $results;
}

$infected = scan_dir('.');
echo "<h1>Scan Results</h1>";
if (empty($infected)) {
    echo "<p style='color:green;'>No suspicious files found 🎉</p>";
} else {
    echo "<ul style='color:red;'>";
    foreach ($infected as $file) {
        echo "<li><strong>Suspicious:</strong> $file</li>";
    }
    echo "</ul>";
}
