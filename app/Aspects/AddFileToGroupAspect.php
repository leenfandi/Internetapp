<?php

namespace App\Aspects;
use App\Models\File;
use App\Models\Groupofuser;
use Illuminate\Support\Facades\Lock;
use Illuminate\Validation\ValidationException;


//////// this Aspect checkowner of file befor do action on it ///////
class AddFileToGroupAspect
{
    public function before( $request){

        $user = auth()->user();
        $ownerId = $user->id;
        $group_us= Groupofuser::where([
            ['user_id',$ownerId],
            ['group_id',$request->group_id]
        ])->first();

        if ($group_us)
        {
            return $request;
        }
        else{
            $error = ValidationException::withMessages([
                'msg' => 'you dont have Access To This File'
            ]);
            throw $error;
        }

    }

    public function after( $response){

       dump(1);

   }
}
