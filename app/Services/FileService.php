<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class FileService
{


    public function getUploadedFiles()
    {
        $files = Storage::allFiles('uploads');

        // Extract only the file names without the full path
        $fileNames = array_map(function ($file) {
            return basename($file);
        }, $files);

        return $fileNames;
    }

    public function uploadFile($request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = 'app\uploads\filesToUpload' . '\\' ;
            // Acquire a lock before accessing the shared resource
            $lockPath =    $filePath. $fileName . '.lock';
           // dd(storage_path($lockPath));
            $lockHandle = fopen(storage_path($lockPath), 'w');
            if (flock($lockHandle, LOCK_EX)) {
                try {
                    $file->storeAs('uploads', random_int(0,50).$fileName); // Store the file in the "uploads" directory
                    // Additional logic or database operations related to the uploaded file
                    return 'File uploaded successfully';
                } finally {
                    flock($lockHandle, LOCK_UN); // Release the lock
                    fclose($lockHandle); // Close the lock file handle
                    unlink(storage_path($lockPath));
                }
            } else {
                fclose($lockHandle); // Close the lock file handle
                return 'Unable to acquire lock';
            }
        }

        return 'No file provided';
    }

    public function deleteFile($fileName)
    {
        $filePath = 'uploads/' . $fileName;
        $filePath2 = 'app\uploads' . '\\' ;
        // Acquire a lock before accessing the shared resource
       // $lockPath = $filePath . '.lock';
        $lockPath =    $filePath2. $fileName . '.lock';

        $lockHandle = fopen(storage_path($lockPath), 'w');
        if (flock($lockHandle, LOCK_EX)) {
            try {
                if (Storage::exists($filePath)) {
                    Storage::delete($filePath); // Delete the file from the "uploads" directory
                    // Additional logic or database operations related to the file deletion
                    return 'File deleted successfully';
                } else {
                    return 'File not found';
                }
            } finally {
                flock($lockHandle, LOCK_UN); // Release the lock
                fclose($lockHandle); // Close the lock file handle
                File::delete(storage_path($lockPath)); // Delete the lock file
            }
        } else {
            fclose($lockHandle); // Close the lock file handle
            return 'Unable to acquire lock';
        }
    }
}
