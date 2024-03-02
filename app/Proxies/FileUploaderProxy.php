<?php
namespace App\Proxies;

use App\Aspects\CheckinAspect;
use App\Aspects\CheckinMultiAspect;
use App\Aspects\CheckoutAspect;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
class FileUploaderProxy extends FileController
{

    protected $CheckoutAspect;
    protected $checkinAspect;
    protected $checkinMultiAspect;
    public function __construct(CheckoutAspect $CheckoutAspect , CheckinAspect $checkinAspect , CheckinMultiAspect $checkinMultiAspect)
    {
        $this->CheckoutAspect = $CheckoutAspect;
        $this->checkinAspect = $checkinAspect;
        $this->checkinMultiAspect = $checkinMultiAspect;
    }

    public function uploadFile(Request $request)
    {

        $fileHandle = $this->CheckoutAspect->before($request);
        FileController::uploadFile($request);
        $this->CheckoutAspect->after($fileHandle);
    }
    public function downloadFile(Request $request)
    {
        $fileHandle = $this->checkinAspect->before($request);
        return FileController::downloadFile($request);

    }
    public function downloadFiles(Request $request)
    {
        $fileHandle = $this->checkinMultiAspect->before($request);
        return FileController::downloadFiles($request);
    }
}
