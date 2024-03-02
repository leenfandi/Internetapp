<?php

namespace App\Aspects;

class CheckBeforAddAspect
{

    public function before($requst)
    {
        $fileLockingAspect = new FileLockingAspect();
        $fileLockingAspect->before($requst);
        $addFileToGroupAspect = new AddFileToGroupAspect();
        $addFileToGroupAspect->before($requst);
    }

    public function after($requst){
        $addFileToGroupAspect = new AddFileToGroupAspect();
        $addFileToGroupAspect->after($requst);
        $fileLockingAspect = new FileLockingAspect();
        $fileLockingAspect->after($requst);
    }

}
