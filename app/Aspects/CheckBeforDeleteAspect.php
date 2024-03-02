<?php

namespace App\Aspects;

class CheckBeforDeleteAspect{

    public function before($requst)
    {
        $FileCheckDeletAspect = new FileCheckDeletAspect();
        $FileCheckDeletAspect->before($requst);
        $addFileToGroupAspect = new AddFileToGroupAspect();
        $addFileToGroupAspect->before($requst);
    }

    public function after($requst){
        $addFileToGroupAspect = new AddFileToGroupAspect();
        $addFileToGroupAspect->after($requst);
        $FileCheckDeletAspect = new FileCheckDeletAspect();
        $FileCheckDeletAspect->after($requst);
    }


}
