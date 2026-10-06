<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
admin_headers();
admin_session();
$_SESSION = [];
session_destroy();
header('Location: /admin/login.php', true, 303);
exit;
