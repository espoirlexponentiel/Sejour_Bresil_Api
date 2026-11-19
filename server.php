<?php

/**
 * Laravel - Built-in Web Server Router for Wasmer & PHP CLI
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? ''
);

// Servir les fichiers statiques s'ils existent dans le dossier public
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

require_once __DIR__ . '/public/index.php';
