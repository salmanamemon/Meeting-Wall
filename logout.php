<?php
require __DIR__ . '/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
verifyCsrf();
logout();
header('Location: login.php');

