<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Storage\StorageClient;
use Exception;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', 
        ]);

        try {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();

            $storage = new StorageClient([
                'keyFilePath' => storage_path('app/private/gcs-key.json'),
            ]);

            $bucketName = 'fixpro-backend';
            $bucket = $storage->bucket($bucketName);

            $bucket->upload(
                fopen($file->getRealPath(), 'r'),
                [
                    'name' => $filename,
                ]
            );

            $object = $bucket->object($filename);
            $url = $object->signedUrl(new \DateTime('+10 minutes'));

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'url' => $url,
            ]);
        } 
        catch (Exception $e) {
            Log::error('GCS Upload Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getFile($filename)
    {
        try {
            $storage = new StorageClient([
                'keyFilePath' => storage_path('app/private/gcs-key.json'),
            ]);

            $bucketName = env('GOOGLE_CLOUD_STORAGE_BUCKET');
            $bucket = $storage->bucket($bucketName);

            $object = $bucket->object($filename);

            if (!$object->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found.',
                ], 404);
            }

            $url = $object->signedUrl(new \DateTime('+10 minutes'));

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            Log::error('GCS Get File Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate download URL.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
