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
    }



    public function status()
    {
        return Upload::orderBy('id', 'desc')->get()->map(function ($u) {
            return [
                'id'        => $u->id,
                'file_name' => $u->file_name,
                'status'    => $u->status,
                'created_at_formatted' => \Carbon\Carbon::parse($u->created_at)->format('d-m-Y g:i a'),
                'created_at_human' => \Carbon\Carbon::parse($u->created_at)->diffForHumans(),
            ];
        });
    }
}
