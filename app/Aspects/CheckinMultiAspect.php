<?php

namespace App\Aspects;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

Class CheckinMultiAspect{
    public function before($request)
    {
        $file_ids = $request->input('ids');

        foreach ($file_ids as $file_id) {
            $fileName = File::find($file_id)->name;
            $lockFilePath = 'public/' . $fileName . '.lock';

            // File exists in the public storage
            if (Storage::exists($lockFilePath)) {
                // Lock file already exists
                dd('Error: Lock file already checked!');
            } else {
                // Add lock file
                Storage::put($lockFilePath, '');
            }
        }
    }
    public function after($request){

    }
}



