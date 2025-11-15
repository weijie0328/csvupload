<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $uploadId;
    public $filePath;
    
    public function __construct($filePath, $uploadId)
    {
        $this->filePath = $filePath;
        $this->uploadId = $uploadId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $path = storage_path('app/' . $this->filePath);
            $csvContent = file_get_contents($path);
            $csvContent = mb_convert_encoding($csvContent, 'UTF-8', 'UTF-8');
            $csvContent = preg_replace('/^\xEF\xBB\xBF/', '', $csvContent);

            $rows = array_map('str_getcsv', explode(PHP_EOL, $csvContent));
            $header = array_map('trim', array_shift($rows));

            foreach ($rows as $row) {
                if (count($row) < count($header)) continue;

                $data = array_combine($header, $row);
                $uniqueKey = $data['UNIQUE_KEY'] ?? $data["\xEF\xBB\xBFUNIQUE_KEY"] ?? null;
                if (!$uniqueKey) continue;

                DB::table('products')->updateOrInsert(
                    ['unique_key' => $uniqueKey],
                    [
                        'product_title'       => $data['PRODUCT_TITLE'] ?? null,
                        'product_description' => $data['PRODUCT_DESCRIPTION'] ?? null,
                        'style'               => $data['STYLE#'] ?? null,
                        'sanmar_mainframe_color' => $data['SANMAR_MAINFRAME_COLOR'] ?? null,
                        'size'                => $data['SIZE'] ?? null,
                        'color_name'          => $data['COLOR_NAME'] ?? null,
                        'piece_price'         => floatval($data['PIECE_PRICE'] ?? 0),
                        'updated_at'          => now(),
                    ]
                );
            }

            Upload::find($this->uploadId)->update(['status' => 'completed']);

            if (file_exists($path)) {
                unlink($path);
            }

        } catch (\Exception $e) {

            $this->fail($e);
            
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("ProcessCsv FAILED: " . $exception->getMessage());
        Log::error($exception->getTraceAsString());

        Upload::find($this->uploadId)->update([
            'status' => 'failed'
        ]);

        $path = storage_path('app/' . $this->filePath);
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
