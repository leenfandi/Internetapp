<?php

namespace App\Aspects;
use Illuminate\Support\Facades\Storage;
class FileLockingAspect
{
    public function before( $request)
    {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = 'public/' . $fileName;
        $lockFilePath = 'public/' . $fileName . '.lock';
            // File exists in the public storage
        if (!Storage::exists($filePath)) {
            if (Storage::exists($lockFilePath)) {
                // Lock file already exists
                dd('Error: Lock file already exists!');
            } else {
                // Add lock file
                Storage::put($lockFilePath, '');
                dump('Lock file added.');
            }
        }
        else {
            // File does not exist in the public storage
            dd('Error: File already exist!');
        }
    }

    public function after($request)
    {
      // Release the lock file

      $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

    $lockFilePath = 'public/' . $fileName . '.lock';

    if (Storage::exists($lockFilePath)) {
        Storage::delete($lockFilePath);
        dd('Lock released.');
    }
    }
}
