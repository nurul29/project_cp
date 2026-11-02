<?php

$publicPath = __DIR__.'/public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Serve .mjs files with proper JavaScript MIME type for ES module workers (e.g. PDF.js)
if ($uri !== '/' && str_ends_with(strtolower($uri), '.mjs') && is_file($publicPath.$uri)) {
    header('Content-Type: text/javascript; charset=utf-8');
    header('Cache-Control: public, max-age=31536000');
    readfile($publicPath.$uri);
    return true;
}

// Serve /storage/* files directly from storage/app/public if symlink is absent on Windows
if (str_starts_with($uri, '/storage/')) {
    $storageBase = realpath(__DIR__.'/storage/app/public');
    $relativePath = substr($uri, strlen('/storage/'));
    $targetFile = $storageBase ? realpath($storageBase.DIRECTORY_SEPARATOR.$relativePath) : false;

    if ($targetFile && str_starts_with($targetFile, $storageBase) && is_file($targetFile)) {
        $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => mime_content_type($targetFile) ?: 'application/octet-stream',
        };
        header('Content-Type: '.$mime);
        header('Content-Length: '.filesize($targetFile));
        header('Content-Disposition: inline; filename="'.basename($targetFile).'"');
        readfile($targetFile);
        return true;
    }
}

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
