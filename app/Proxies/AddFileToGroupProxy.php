<?php
namespace App\Proxies;

use App\Aspects\CheckBeforAddAspect;
use App\Aspects\CheckBeforDeleteAspect;
use App\Http\Controllers\GroupController;
use Illuminate\Http\Request;

class AddFileToGroupProxy extends GroupController
{
    protected $CheckBeforAddAspect;
    protected $CheckBeforDeleteAspect;

    public function __construct( CheckBeforAddAspect $CheckBeforAddAspect, CheckBeforDeleteAspect $CheckBeforDeleteAspect)
    {
        $this->CheckBeforAddAspect = $CheckBeforAddAspect;
        $this->CheckBeforDeleteAspect = $CheckBeforDeleteAspect;

    }

    public function addfile_to_group(Request $request){
        $fileHandel = $this->CheckBeforAddAspect->before($request);
        GroupController::addfile_to_group($request);
        $this->CheckBeforAddAspect->after($request);

    }
    public function deletefile_from_group(Request $request)
    {
        $fileHandel = $this->CheckBeforDeleteAspect->before($request);
        GroupController::deletefile_from_group($request);
        $this->CheckBeforDeleteAspect->after($request);
    }
}
