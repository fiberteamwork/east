<?php

namespace App\Controllers;

class MapDataController extends BaseController
{
    private const MAX_UPLOAD_BYTES = 25 * 1024 * 1024;

    public function index()
    {
        $directory = WRITEPATH . 'map_uploads';
        $files = [];

        if (is_dir($directory)) {
            foreach (glob($directory . DIRECTORY_SEPARATOR . '*.xlsx') ?: [] as $file) {
                $name = basename($file);
                if (preg_match('/\A\d{8}_[a-f0-9]{24}\.xlsx\z/', $name) !== 1) {
                    continue;
                }

                $files[] = ['url' => site_url('map-data/' . $name)];
            }
        }

        return $this->response->setJSON(['files' => $files]);
    }

    public function serve(string $filename)
    {
        if (preg_match('/\A\d{8}_[a-f0-9]{24}\.xlsx\z/', $filename) !== 1) {
            return $this->response->setStatusCode(404);
        }

        $directory = realpath(WRITEPATH . 'map_uploads');
        $file = realpath(WRITEPATH . 'map_uploads' . DIRECTORY_SEPARATOR . $filename);

        if ($directory === false || $file === false || ! is_file($file)
            || ! str_starts_with(strtolower($file), strtolower($directory . DIRECTORY_SEPARATOR))) {
            return $this->response->setStatusCode(404);
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read the uploaded map data workbook.');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Length', (string) strlen($contents))
            ->setHeader('Cache-Control', 'private, no-cache, must-revalidate')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($contents);
    }

    public function upload()
    {
        $file = $this->request->getFile('excel_file');
        if ($file === null || ! $file->isValid()) {
            $error = $file === null ? 'Pilih file Excel untuk diunggah.' : $file->getErrorString();

            return redirect()->to('/admin/upload')->with('mapDataErrors', [$error]);
        }

        if (strtolower($file->getExtension()) !== 'xlsx') {
            return redirect()->to('/admin/upload')->with('mapDataErrors', ['Format file harus .xlsx.']);
        }

        if ($file->getSize() > self::MAX_UPLOAD_BYTES) {
            return redirect()->to('/admin/upload')->with('mapDataErrors', ['Ukuran file maksimal 25 MB.']);
        }

        $handle = fopen($file->getTempName(), 'rb');
        $signature = $handle === false ? false : fread($handle, 4);
        if (is_resource($handle)) {
            fclose($handle);
        }
        if ($signature !== "PK\x03\x04") {
            return redirect()->to('/admin/upload')->with('mapDataErrors', ['File yang dipilih bukan workbook .xlsx yang valid.']);
        }

        $directory = WRITEPATH . 'map_uploads';
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Unable to create the uploaded map data directory.');
        }

        $filename = date('Ymd') . '_' . bin2hex(random_bytes(12)) . '.xlsx';
        $file->move($directory, $filename);

        return redirect()->to('/admin/upload')->with('mapDataNotice', 'File Excel berhasil diunggah dan akan ditambahkan ke data peta.');
    }
}
