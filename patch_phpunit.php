<?php
$content = file_get_contents('phpunit.xml');
$content = str_replace(
    '<env name="DB_CONNECTION" value="testing"/>',
    '<env name="DB_CONNECTION" value="pgsql"/>',
    $content
);
file_put_contents('phpunit.xml', $content);
