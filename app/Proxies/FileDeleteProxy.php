<?php
namespace App\Proxies;

use App\Aspects\FileCheckDeletAspect;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
class FileDeleteProxy extends FileController
{

    protected $fileCheckDeletAspect;

    public function __construct(FileCheckDeletAspect $fileCheckDeletAspect)
    {
        $this->fileCheckDeletAspect = $fileCheckDeletAspect;
    }

    public function deleteFile(Request $request)
    {
        $fileHandle = $this->fileCheckDeletAspect->beforeDelete($request);
        FileController::deleteFile($request);
        $this->fileCheckDeletAspect->afterDelete($fileHandle);
    }
}
