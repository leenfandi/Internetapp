<?php

namespace App\Aspects;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

Class CheckoutAspect{

    public function before($request)
    {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = 'public/' . $fileName;
        $lockFilePath = 'public/' . $fileName . '.lock';
            // File exists in the public storage
        if (Storage::exists($filePath)) {
            if (Storage::exists($lockFilePath)) {
                Storage::delete($lockFilePath);
                dump('Lock released.');
            } else {
                dump('checkin first');
            }
        }
        else {
            // File does not exist in the public storage
            dd('Error: this is new file please add it to group first');
        }
    }
    public function after($request){
        dd('done');
    }

}


