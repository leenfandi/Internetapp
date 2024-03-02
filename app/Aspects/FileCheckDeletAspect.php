<?php

namespace App\Aspects;
use App\Models\File;
use Illuminate\Support\Facades\Lock;
use Illuminate\Support\Facades\Storage;

class FileCheckDeletAspect
{
    public function before( $request)
    {
        $fileName = File::find($request->id)->name;
        $lockFilePath = 'public/' . $fileName . '.lock';
        if (Storage::exists($lockFilePath)) {
            // Lock file already exists
            dd('Error: Lock file already exists!');
        } else {
            // Add lock file
            Storage::put($lockFilePath, '');

            dump('Lock file added.');
            return $fileName ;
        }

    }

    public function after( $request)
    {
        $fileName = $request->name;
        $lockFilePath = 'public/' . $fileName . '.lock';
        if (Storage::exists($lockFilePath)) {
            Storage::delete($lockFilePath);
            dd('Lock released.');
        }
    }
}
