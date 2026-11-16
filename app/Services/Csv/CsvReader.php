<?php

namespace App\Services\Csv;

use Illuminate\Http\UploadedFile;

/** Reads an uploaded CSV into rows keyed by their line number, using the header row as column names. */
class CsvReader
{
    /**
     * @param  list<string>  $required  Columns the file must contain.
     * @return array{rows: array<int, array<string, string>>, errors: list<string>}
     */
    public function read(UploadedFile $file, array $required, int $maxRows, string $noun): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return ['rows' => [], 'errors' => ['The file could not be read.']];
        }

        $header = fgetcsv($handle);

        if (is_array($header) && isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header ?: []);
        $missing = array_diff($required, $header);

        if ($missing !== []) {
            fclose($handle);

            return ['rows' => [], 'errors' => ['Missing column(s): '.implode(', ', $missing).'.']];
        }

        $rows = [];
        $line = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $line++;

            if (count(array_filter($values, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            if (count($rows) >= $maxRows) {
                fclose($handle);

                return ['rows' => [], 'errors' => ["A CSV can contain at most {$maxRows} {$noun}."]];
            }

            $width = count($header);
            $rows[$line] = array_map('trim', array_combine($header, array_pad(array_slice($values, 0, $width), $width, '')));
        }

        fclose($handle);

        return $rows === []
            ? ['rows' => [], 'errors' => ["The CSV has no {$noun}."]]
            : ['rows' => $rows, 'errors' => []];
    }
}
