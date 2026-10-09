<?php

namespace App\Controllers;

class CsvController extends BaseController
{
    public function save()
    {
        $input = $this->request->getBody();
        if ($input === '') {
            return $this->response->setStatusCode(400)->setBody('NO_INPUT');
        }

        $input = preg_replace('/\r\n|\r/', "\n", $input);
        $input = preg_replace('/^\xEF\xBB\xBF/', '', $input);
        $target = ROOTPATH . 'data' . DIRECTORY_SEPARATOR . 'customer.csv';

        if (! is_dir(dirname($target)) && ! mkdir(dirname($target), 0775, true) && ! is_dir(dirname($target))) {
            throw new \RuntimeException('Unable to create the customer data directory.');
        }

        if ($this->request->getGet('append') === '1' || $this->request->getGet('append') === 'true') {
            return $this->appendCsv($target, $input);
        }

        if (is_file($target) && ! copy($target, $target . '.bak_' . date('Ymd_His'))) {
            throw new \RuntimeException('Unable to create a backup of the current customer CSV.');
        }

        $temporary = $target . '.tmp';
        if (file_put_contents($temporary, $input, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to write the customer CSV temporary file.');
        }
        if (! rename($temporary, $target)) {
            @unlink($temporary);
            throw new \RuntimeException('Unable to replace the customer CSV file.');
        }

        return $this->response->setContentType('text/plain')->setBody('OK');
    }

    private function appendCsv(string $target, string $input)
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $input)), static fn (string $line): bool => $line !== ''));
        if ($lines === []) {
            return $this->response->setStatusCode(400)->setBody('NO_INPUT_LINES');
        }

        if (is_file($target) && ! copy($target, $target . '.bak_' . date('Ymd_His'))) {
            throw new \RuntimeException('Unable to create a backup of the current customer CSV.');
        }

        $handle = fopen($target, 'c+');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open the customer CSV for appending.');
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new \RuntimeException('Unable to lock the customer CSV for appending.');
            }

            $existing = stream_get_contents($handle);
            if ($existing === false) {
                throw new \RuntimeException('Unable to read the current customer CSV.');
            }

            $existingLines = array_values(array_filter(explode("\n", $existing), static fn (string $line): bool => trim($line) !== ''));
            $normalize = static function (string $line): string {
                return mb_strtolower(preg_replace('/\s+/', ' ', trim(preg_replace('/^\xEF\xBB\xBF/', '', $line))));
            };
            if ($existingLines !== [] && $normalize($existingLines[0]) === $normalize($lines[0])) {
                array_shift($lines);
            }

            if ($lines !== []) {
                if (fseek($handle, 0, SEEK_END) !== 0) {
                    throw new \RuntimeException('Unable to seek to the end of the customer CSV.');
                }
                $prefix = $existing !== '' && ! str_ends_with($existing, "\n") ? "\n" : '';
                if (fwrite($handle, $prefix . implode("\n", $lines) . "\n") === false) {
                    throw new \RuntimeException('Unable to append to the customer CSV.');
                }
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        return $this->response->setContentType('text/plain')->setBody('OK');
    }
}
