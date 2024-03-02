<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\File;
use App\Models\Group;
use App\Models\Groupofuser;
use ZipArchive;
use App\Listeners\LoginListener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpWord\IOFactory;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


class FileController extends Controller
{
    public function uploadFile(Request $request)
    {
    $user = auth()->user();
    $userId = $user->id;
  if ($request->hasFile('file')) {
      $file = $request->file('file');
      $fileName = $file->getClientOriginalName();
      $filePath = 'public/' . $fileName;
      if (Storage::exists($filePath)){

        Storage::delete($filePath);
      }
      else{
        $error = ValidationException::withMessages([
            'msg' => 'shore that you use the origin name'
        ]);
        throw $error;
      }
      $filePath =  $file->storeAs('public', $fileName);
      return response()->json("File added to public storage successfully", 200);
  } else {
      $error = ValidationException::withMessages([
          'msg' => 'you dont have Access To This File'
      ]);
      throw $error;
  }
}
    public function updateStatus(Request $request, $id)
    {
        $file = File::find($id);
        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $file->status = $request->input('status');
        $file->save();

        return response()->json(['message' => 'File status updated successfully'], 200);
    }

    public function reserveFile(Request $request)
    {
        $fileId = $request->input('id');

        $file = File::where('id', $fileId)->lockForUpdate()->first();

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        if ($file->status === 1) {
            return response()->json(['message' => 'Sorry, File already reserved'], 400);
        }

        $file->status = 1;
        $file->save();

        return response()->json(['message' => 'File reserved successfully'], 200);
    }
    public function cancelReservation(Request $request)
{
    $fileId = $request->input('id');

    $file = File::where('id', $fileId)->lockForUpdate()->first();

    if (!$file) {
        return response()->json(['message' => 'File not found'], 404);
    }

    if ($file->status === 0) {
        return response()->json(['message' => 'File is not reserved'], 400);
    }

    $file->status = 0;
    $file->save();

    return response()->json(['message' => 'Reservation canceled successfully'], 200);
}




    public function deleteFile(Request $request)
{
    $id = $request->id;
    try {
        \Log::info('Received DELETE request for file ID: ' . $id);

        $fileId = $id;
        \Log::info('File ID from route parameter: ' . $fileId);

        $file = File::find($fileId);
        \Log::info('File Object: ' . json_encode($file));

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        if ($file->status == 0) {
            $filePath = 'public' . $file->path;
            \Log::info('Complete File Path: ' . public_path($filePath));


            \Log::info('File Exists: ' . (Storage::disk('public')->exists($filePath) ? 'Yes' : 'No'));


            $deleteResult = Storage::disk('public')->delete($filePath);


            \Log::info('File Deletion Result: ' . json_encode($deleteResult));


            $result = $file->delete();
            \Log::info('File deleted successfully.');
            return response()->json(['message' => 'File deleted successfully']);
        } else {
            \Log::info('File is not free.');
            return response()->json(['message' => 'File is not free'], 400);
        }
    } catch (\Exception $e) {
        \Log::error('Exception: ' . $e->getMessage());
        return response()->json(['message' => $e->getMessage()], 500);
    }
}
public function downloadFile(Request $request)
{
    $file_id = $request->id;
    $file = File::find($file_id);
    $file_name= $file->name;
    $file_path = $file->path;
    $file_path = 'app/'. $file_path;
    $filePath = storage_path($file_path);
    $fileName = $file_name ; // Optional, set a custom file name
    return Response::download($filePath, $fileName);
}



//use Illuminate\Support\Facades\File;

public function downloadFiles(Request $request)
{
    $file_ids = $request->ids;
    $files = File::whereIn('id', $file_ids)->get();
    $zip = new \ZipArchive();
    $zipFileName = 'files.zip';
    $zipFilePath = storage_path($zipFileName);
    $zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

    foreach ($files as $file) {
        $fileName = $file->name;
        $filePath = $file->path;
        $fullPath = storage_path('app/' . $filePath);
        if (file_exists($fullPath)) {
            $zip->addFile($fullPath, $fileName);
        }
    }

    $zip->close();

    return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
}


public function showFilesInGroup(Request $request)
{
    $group_id = $request->input('group_id');

    $cacheKey = 'files_in_group_' . $group_id;
    $files = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($group_id) {
        $group = Group::find($group_id);

        if (!$group) {
            return [];
        }

        return File::whereHas('groupoffiles', function ($query) use ($group_id) {
            $query->where('group_id', $group_id);
        })->orderBy('id', 'asc')->get();
    });

    $fileData = [];

    foreach ($files as $file) {
        $userData = null;

        if ($file->status == 1) {
            $reservedBy = $file->user;

            if ($reservedBy) {
                $userData = [
                    'user_id' => $reservedBy->id,
                    'username' => $reservedBy->name,
                ];
            }
        }

        $fileData[] = [
            'file_id' => $file->id,
            'file_name' => $file->name,
            'file_path' =>$file->path,
            'status' => $file->status,
            'reserved_by' => $userData,
        ];
    }

    return response()->json([
        'message' => 'These are the files contained in the group',
        'files' => $fileData,
    ], 200);

}
public function replaceFile(Request $request, $fileId)
{

    $existingFile = File::find($fileId);

    if (!$existingFile) {
        return response()->json(['message' => 'File not found'], 404);
    }

    $existingFile->name = $request->input('new_file_name');

    if ($request->hasFile('new_file')) {
        $newFile = $request->file('new_file');
        $newFileName = $newFile->getClientOriginalName();


        $newFile->storeAs('public', $newFileName);

        $existingFile->path = 'public/' . $newFileName;
    }

    $existingFile->save();

    return response()->json(['message' => 'File replaced successfully'], 200);
}


public function getData($city) {


    $apiKey = '5a6ea0b5c8333fb264b66c8aeef6ad1b';
    $url = "http://api.openweathermap.org/data/2.5/weather?q=".$city."&appid=".$apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch , CURLOPT_HEADER,0 );
    curl_setopt($ch , CURLOPT_FOLLOWLOCATION,1 );
    curl_setopt($ch , CURLOPT_VERBOSE,0 );


    $result = curl_exec($ch);

    curl_close($ch);

    return response()->json(json_decode($result));
}



/*public function downloadManyFiles(Request $request)
{
    if (!$request->hasFile('files')) {
        return response()->json(['message' => 'You have not selected any files'], 403);
    }

    $files = $request->file('files');
    $desktopFolder = $request->input('desktop_folder');
    $desktopPath = 'C:/Users/ASUS/Desktop/' . $desktopFolder . '/';
    $uploadedFiles = [];

    try {
        Storage::makeDirectory($desktopPath);
    } catch (\Exception $e) {
        Log::error('Error creating directory: ' . $desktopPath . ': ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        return response()->json(['message' => 'Error occurred while creating directory'], 500);
    }

    foreach ($files as $file) {
        $filename = $file->getClientOriginalName();
        $desktopFile = $desktopPath . $filename;

        Log::info('Public File Path: ' . $file->getRealPath());
        Log::info('Desktop File Path: ' . $desktopFile);

        try {
            $result = Storage::putFileAs($desktopPath, $file, $filename);

            if (!$result) {
                Log::error('Error copying file: ' . $filename);
                return response()->json(['message' => 'Cannot download one or more files'], 403);
            }

            $uploadedFiles[] = $filename;
        } catch (\Exception $e) {
            Log::error('Error copying file: ' . $filename . ': ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['message' => 'Error occurred while downloading files'], 500);
        }
    }

    Log::info('Files download completed');

    return response()->json(['message' => 'Files download completed', 'uploaded_files' => $uploadedFiles], 200);
}*/

        }

