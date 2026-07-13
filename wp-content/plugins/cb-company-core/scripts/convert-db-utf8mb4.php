<?php
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script is CLI-only.\n");
    exit(1);
}

require '/var/www/html/wp-load.php';

global $wpdb;

$database = str_replace('`', '``', DB_NAME);
$wpdb->query("ALTER DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

foreach ($wpdb->get_col('SHOW TABLES') as $table) {
    $safe_table = str_replace('`', '``', $table);
    $result = $wpdb->query("ALTER TABLE `{$safe_table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if ($result === false) {
        fwrite(STDERR, "Failed: {$table}\n");
        exit(1);
    }
    fwrite(STDOUT, "Converted: {$table}\n");
}
