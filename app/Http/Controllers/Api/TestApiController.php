<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class TestApiController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'note' => 'nullable|string',
            'files' => 'required|array',
            'files.*.filename' => 'required|string',
            'files.*.content' => 'required|string', // base64 string
        ]);

        $savedPaths = [];

        foreach ($request->input('files') as $file) {
            $base64 = $file['content'];
            $filename = $file['filename'];

            if (preg_match('/^data:.*;base64,/', $base64)) {
                $base64 = substr($base64, strpos($base64, ',') + 1);
            }

            $decoded = base64_decode($base64);

            if ($decoded === false) {
                return response()->json(['error' => 'Invalid base64 data.'], 400);
            }

            $path = 'uploads/' . Str::random(10) . '_' . $filename;
            Storage::put($path, $decoded);
            $savedPaths[] = $path;
        }

        return response()->json([
            'message' => 'Upload successful',
            'saved_files' => $savedPaths,
            'user_id' => $request->input('user_id'),
            'note' => $request->input('note'),
        ]);
    }
}