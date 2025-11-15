<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Upload;
use App\Models\Product;
use App\Jobs\ProcessCsv;

class CsvController extends Controller
{
    public function index()
    {
        return view('csv-upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);
        
        $file = $request->file('file');
        $path = $file->store('csv_uploads');

        // Log upload
        $upload = Upload::create([
            'file_name' => $file->getClientOriginalName(),
            'status' => 'processing'
        ]);

        dispatch(new ProcessCsv($path, $upload->id));

        return response()->json([
            'success' => true,
            'message' => 'Upload received — processing in background'
        ]);

        // try {
        //     // Read CSV
        //     $csv = file_get_contents($file->getRealPath());

        //     // Clean invalid UTF-8
        //     $csv = mb_convert_encoding($csv, 'UTF-8', 'UTF-8');

        //     // Remove UTF-8 BOM if exists
        //     $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);

        //     $rows = array_map('str_getcsv', explode("\n", $csv));
        //     $header = array_shift($rows);
        //     $header = array_map(function($h) {
        //         return preg_replace('/^\xEF\xBB\xBF/', '', trim($h));
        //     }, $header);

        //     foreach ($rows as $row) 
        //     {
        //         if (count($row) < count($header)) continue;

        //         $data = array_combine($header, $row);
        //         if (!$data) continue;

        //         // Determine unique key (CSV has capital)
        //         $uniqueKey = $data['UNIQUE_KEY'] ?? $data["\xEF\xBB\xBFUNIQUE_KEY"] ?? null;
        //         if (!$uniqueKey) continue;

        //         // Map CSV → DB columns
        //         $insertData = [
        //             'product_title'          => $data['PRODUCT_TITLE'] ?? null,
        //             'product_description'    => $data['PRODUCT_DESCRIPTION'] ?? null,
        //             'style'                  => $data['STYLE#'] ?? null,
        //             'sanmar_mainframe_color' => $data['SANMAR_MAINFRAME_COLOR'] ?? null,
        //             'size'                   => $data['SIZE'] ?? null,
        //             'color_name'             => $data['COLOR_NAME'] ?? null,
        //             'piece_price'            => isset($data['PIECE_PRICE']) ? floatval($data['PIECE_PRICE']) : null,
        //             'updated_at'             => now(),
        //             'created_at'             => now(),
        //         ];

        //         DB::table('products')->updateOrInsert(
        //             ['unique_key' => $uniqueKey],
        //             $insertData
        //         );
        //     }

        //     $upload->update(['status' => 'completed']);

        //     return response()->json([
        //         'success' => true,
        //         'message' => "Import completed"
        //     ]);

        // } catch (\Exception $e) {

        //     $upload->update(['status' => 'failed']);

        //     return response()->json([
        //         'success' => false,
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }



    public function status()
    {
        return Upload::orderBy('id', 'desc')->get();
    }
}
