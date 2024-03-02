<?php

namespace App\Http\Controllers;

use App\Facades\FileFacade;
use Illuminate\Http\Request;

class FileControllerPC extends Controller
{

    public function index()
    {
        $uploadedFiles =FileFacade::getUploadedFiles();

        return view('files.index', ['files' => $uploadedFiles]);
    }

    public function upload(Request $request)
    {
        // Call the FileFacade to upload the file
        $result = FileFacade::uploadFile($request);

        return response()->json(['message' => $result]);
    }

    public function delete($fileName)
    {
        // Call the FileFacade to delete the file
        $result = FileFacade::deleteFile($fileName);

        return response()->json(['message' => $result]);
    }
}
