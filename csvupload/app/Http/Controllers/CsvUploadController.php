<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CsvController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');

        // Read raw CSV content
        $csvContent = file_get_contents($file->getRealPath());

        // Clean invalid UTF-8 characters
        $csvContent = mb_convert_encoding($csvContent, 'UTF-8', 'UTF-8');

        // Convert CSV to array rows
        $rows = array_map('str_getcsv', explode(PHP_EOL, $csvContent));
        $header = array_shift($rows); // header row

        foreach ($rows as $row) {
            if (count($row) < count($header)) {
                continue;
            }

            $data = array_combine($header, $row);
            if (!isset($data['UNIQUE_KEY'])) continue;

            DB::table('products')->updateOrInsert(
                ['unique_key' => $data['UNIQUE_KEY']],
                [
                    'product_title'       => $data['PRODUCT_TITLE'] ?? '',
                    'product_description' => $data['PRODUCT_DESCRIPTION'] ?? '',
                    'style'               => $data['STYLE#'] ?? '',
                    'color'               => $data['SANMAR_MAINFRAME_COLOR'] ?? '',
                    'size'                => $data['SIZE'] ?? '',
                    'color_name'          => $data['COLOR_NAME'] ?? '',
                    'piece_price'         => $data['PIECE_PRICE'] ?? 0,
                    'updated_at'          => now(),
                    'created_at'          => now(),
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'CSV processed']);
    }
}
