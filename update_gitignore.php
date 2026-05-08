<?php
$content = file_get_contents('.gitignore');
$lines = explode("\n", $content);
$add = ['storage/', 'bootstrap/cache/', 'node_modules/', 'vendor/', 'public/build/', 'docker/', '.env', 'composer.lock', 'package-lock.json', 'tests/Feature/Auth/', 'tests/Feature/ProfileTest.php'];
foreach ($add as $line) {
    if (!in_array($line, $lines)) {
        $content .= "\n" . $line;
    }
}
file_put_contents('.gitignore', $content);
