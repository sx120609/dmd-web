<?php
require __DIR__ . '/bootstrap.php';

$user = current_user($mysqli);

if (!$user) {
    json_response(['user' => null]);
}

json_response(['user' => $user]);
