<?php

namespace App\Aspects;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

Class CheckinAspect
{
    public function before($request)
    {

        $file_id = $request->id;
        $fileName = File::find($file_id)->name;
        $lockFilePath = 'public/' . $fileName . '.lock';
            // File exists in the public storage
            if (Storage::exists($lockFilePath)) {
                // Lock file already exists
                dd('Error: Lock file already checkin!');
            } else {
                // Add lock file
                Storage::put($lockFilePath, '');

            }
    }
    public function after($request){

    }

}
