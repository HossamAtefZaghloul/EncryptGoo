<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\FileEncryptionService;

class FileController extends Controller
{
    protected $encryptionService;

    public function showUploadForm($view)
    {
        if ($view === 'encrypt') {
            return view('encrypt');
        } elseif ($view === 'decrypt') {
            return view('decrypt');
        } else {
            abort(404); 
        }
    }
    
    public function __construct(FileEncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    protected function encryptFileContent($filePath)
    {
        return $this->encryptionService->encryptFile($filePath);
    }

    protected function decryptFileContent($filePath)
    {
        return $this->encryptionService->decryptFile($filePath);
    }

    public function uploadFile(Request $request)
    {
        \Log::info('Upload file reached', ['data' => $request->all()]);
    
        try {
            // Validate the incoming request
            $request->validate([
                'chunk' => 'required|file',
                'chunkIndex' => 'required|integer',
                'totalChunks' => 'required|integer',
                'fileName' => 'required|string',
            ]);
    
            $chunk = $request->file('chunk');
            $chunkIndex = (int) $request->input('chunkIndex');
            $totalChunks = (int) $request->input('totalChunks');
            $fileName = pathinfo($request->input('fileName'), PATHINFO_FILENAME);
            $fileExtension = pathinfo($request->input('fileName'), PATHINFO_EXTENSION);
    
            $fileName = $this->sanitizeFileName($fileName);
            
            $tempDir = storage_path("app/temp/{$fileName}");
    
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
    
            $chunk->move($tempDir, "chunk_{$chunkIndex}");
            \Log::info("Chunk {$chunkIndex} saved", ['tempDir' => $tempDir]);
    
            $filesInDir = array_diff(scandir($tempDir), ['.', '..']);
            $chunkCount = count($filesInDir);
    
            if ($chunkCount === $totalChunks) {
                \Log::info('All chunks received', ['fileName' => $fileName]);
    
                $assembledFilePath = storage_path("app/temp/{$fileName}.assembled");
                $assembledFile = fopen($assembledFilePath, 'wb');
    
                try {
                    for ($i = 0; $i < $totalChunks; $i++) {
                        $chunkPath = "{$tempDir}/chunk_{$i}";
    
                        if (!file_exists($chunkPath)) {
                            \Log::error("Missing chunk {$i}", ['chunkPath' => $chunkPath]);
                            throw new \Exception("Missing chunk {$i}");
                        }
    
                        $handle = fopen($chunkPath, 'rb');
                        while (!feof($handle)) {
                            fwrite($assembledFile, fread($handle, 8192)); 
                        }
                        fclose($handle);
                    }
                } finally {
                    fclose($assembledFile);
                }
    
                $encryptedContent = $this->encryptFileContent($assembledFilePath);
    
                $encryptedFolder = storage_path('app/encrypted');
                if (!is_dir($encryptedFolder)) {
                    mkdir($encryptedFolder, 0755, true);
                }
    
                $uniqueFileName = "{$fileName}.enc";
                $counter = 1;
    
                while (file_exists(storage_path("app/encrypted/{$uniqueFileName}"))) {
                    $uniqueFileName = "{$fileName}_{$counter}.enc";
                    $counter++;
                }
    
                // Save the encrypted file
                Storage::put("encrypted/{$uniqueFileName}", $encryptedContent);
    
                array_map('unlink', glob("{$tempDir}/chunk_*"));
                rmdir($tempDir); 
    
                if (file_exists($assembledFilePath)) {
                    unlink($assembledFilePath);
                }
    
                return response()->json([
                    'message' => 'File encrypted and saved successfully.',
                    'encrypted_filename' => $uniqueFileName,
                    'download_url' => route('download.file', ['filename' => urlencode($uniqueFileName)])
                ]);
            }
    
            return response()->json(['message' => 'Chunk received.'], 200);
    
        } catch (\Exception $e) {
            \Log::error('Error during file upload', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors('Failed to upload and encrypt the file: ' . $e->getMessage());
        }
    }
    
    public function uploadEncryptedFileChunked(Request $request)
    {
        \Log::info('Chunked decryption upload started', ['data' => $request->all()]);
    
        try {
            // Validate
            $request->validate([
                'chunk' => 'required|file',
                'chunkIndex' => 'required|integer',
                'totalChunks' => 'required|integer',
                'fileName' => 'required|string',
            ]);
    
            $chunk = $request->file('chunk');
            $chunkIndex = (int) $request->input('chunkIndex');
            $totalChunks = (int) $request->input('totalChunks');
            $fileName = pathinfo($request->input('fileName'), PATHINFO_FILENAME);
    
            $fileName = $this->sanitizeFileName($fileName);
    
            $tempDir = storage_path("app/temp/{$fileName}");
    
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
    
            $chunk->move($tempDir, "chunk_{$chunkIndex}");
            \Log::info("Chunk {$chunkIndex} saved", ['tempDir' => $tempDir]);
    
            $filesInDir = array_diff(scandir($tempDir), ['.', '..']);
            $chunkCount = count($filesInDir);
    
            if ($chunkCount === $totalChunks) {
                \Log::info('All chunks for decryption received', ['fileName' => $fileName]);
    
                $assembledFilePath = storage_path("app/decrypted/{$fileName}.assembled");
                $assembledFile = fopen($assembledFilePath, 'wb');
    
                for ($i = 0; $i < $totalChunks; $i++) {
                    $chunkPath = "{$tempDir}/chunk_{$i}";
    
                    if (!file_exists($chunkPath)) {
                        \Log::error("Missing chunk {$i}", ['chunkPath' => $chunkPath]);
                        return response()->json(['error' => "Missing chunk {$i}"], 400);
                    }
    
                    $handle = fopen($chunkPath, 'rb');
                    while (!feof($handle)) {
                        fwrite($assembledFile, fread($handle, 8192)); 
                    }
                    fclose($handle);
                }
    
                fclose($assembledFile);
    
                $decryptedContent = $this->decryptFileContent($assembledFilePath);
    
                $decryptedTempPath = storage_path("app/decrypted/temp_{$fileName}");
                file_put_contents($decryptedTempPath, $decryptedContent);
    
                $fileType = mime_content_type($decryptedTempPath); 
                $extension = $this->getExtensionFromMimeType($fileType);
    
                if (!$extension) {
                    throw new \Exception("Unable to determine file extension for MIME type: {$fileType}");
                }
    
                $finalFileName = "{$fileName}.{$extension}";
    
                Storage::put("decrypted/{$finalFileName}", $decryptedContent);
    
                array_map('unlink', glob("{$tempDir}/chunk_*"));
                rmdir($tempDir);
                unlink($assembledFilePath);
                unlink($decryptedTempPath);
    
                return response()->json([
                    'message' => 'File decrypted successfully.',
                    'decrypted_filename' => $finalFileName,
                    'download_url' => route('downloadDecrypted.file', ['filename' => urlencode($finalFileName)]),
                ]);
            }
    
            return response()->json(['message' => 'Chunk received.'], 200);
    
        } catch (\Exception $e) {
            \Log::error('Error during chunked decryption upload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to upload and decrypt the file: ' . $e->getMessage()], 500);
        }
    }

    private function getExtensionFromMimeType($mimeType)
    {
    $mimeMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf',
        'text/plain' => 'txt',
        'audio/mp3' => 'mp3',
        'audio/mp4' => 'mp4',
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'audio/ogg' => 'oga',
        'video/mp4' => 'mp4',

        // Add other types as needed ........
    ];
    return $mimeMap[$mimeType] ?? null;
    }

    private function sanitizeFileName($fileName)
    {
        // Replace special characters with safe character
        return preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $fileName);
    }
    

    public function downloadFile($filename)
    {
        $filePath = "encrypted/{$filename}";
    
        if (!Storage::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }
    
        return Storage::download($filePath, $filename);
    }

    public function downloadDecrypted($filename)
    {
        $filePath = "decrypted/{$filename}";
    
        if (!Storage::exists($filePath)) {
            return back()->withErrors('File not found.');
        }
    
        return Storage::download($filePath, $filename);
    }
}