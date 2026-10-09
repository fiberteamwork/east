<?php

namespace App\Controllers;

class AssetController extends BaseController
{
    private const DIRECTORIES = ['data', 'layers', 'resources', 'styles', 'webfonts'];

    private const CONTENT_TYPES = [
        'css'   => 'text/css; charset=UTF-8',
        'gif'   => 'image/gif',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'js'    => 'text/javascript; charset=UTF-8',
        'json'  => 'application/json; charset=UTF-8',
        'map'   => 'application/json; charset=UTF-8',
        'png'   => 'image/png',
        'ps1'   => 'text/plain; charset=UTF-8',
        'svg'   => 'image/svg+xml',
        'ttf'   => 'font/ttf',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function serve(string $directory, string $relativePath)
    {
        if (! in_array($directory, self::DIRECTORIES, true)) {
            return $this->response->setStatusCode(404);
        }

        $assetRoot = realpath(ROOTPATH . $directory);
        $file = realpath(ROOTPATH . $directory . DIRECTORY_SEPARATOR . $relativePath);

        if ($assetRoot === false || $file === false || ! is_file($file)) {
            return $this->response->setStatusCode(404);
        }

        $pathPrefix = rtrim($assetRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (! str_starts_with(strtolower($file), strtolower($pathPrefix))) {
            return $this->response->setStatusCode(404);
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read the requested application asset.');
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mimeType = self::CONTENT_TYPES[$extension] ?? 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', (string) strlen($contents))
            ->setHeader('Cache-Control', 'private, no-cache, must-revalidate')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($contents);
    }
}
