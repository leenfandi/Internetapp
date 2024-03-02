<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\File;
use App\Models\Group;
use App\Models\Groupofuser;
use App\Models\Groupoffile;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    public function addgroup(Request $request){

        $user = auth()->user();
        $userId = $user->id;
        $input = $request->all();
        $group = new Group();

      $group->name = $input['name'];
      $group->owner = $userId;
      $group->save();

      return response()->json([
         'messege'=> 'Group create seccesfuly ',
         'name' =>   $group->name ,
         'owner' => $group->owner,
      ]);
      }
      public function adduser(Request $request)
      {
          $user = auth()->user();
          $ownerId = $user->id;
          $input = $request->all();
          $username = new User();
          $group = new Group();

          $group = Group::find($input['group_id']);

          if (!$group) {
              return response()->json([
                  'message' => 'Group not found',
              ], 404);
          }

          if ($group->owner !== $ownerId) {
              return response()->json([
                  'message' => 'You are not the owner of this group. Only the owner can add users.',
              ], 403);
          }

          $username->name = $input['name'];
          $group->id = $input['group_id'];

          $user = User::where('name', $username->name)->first();

          if (!$user) {
              return response()->json([
                  'message' => 'User not found',
              ], 404);
          }

          $group_of_users = Groupofuser::create([
              'user_id' => $user->id,
              'group_id' => $request->group_id,
          ]);

          return response()->json([
              'message' => 'User added',
              'data' => [
                  'id' => $user->id,
                  'name' => $user->name,
                  'group_id' => $request->group_id,
              ],
          ], 200);
      }
      public function addfile_to_group(Request $request)
      {
          $user = auth()->user();
          $userId = $user->id;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            // Move the uploaded file to the public storage path
            $filePath =  $file->storeAs('public', $fileName);
            $filemodel = File::create(
                [
                  "name"=>  $fileName ,
                  "path"=> $filePath,
                    "user_id"=>$userId,
                    "status"=> 0
                ]
             );
             $group_id = Group::find($request->group_id)->id ;

             if(!$group_id){
               return response()->json("no such group", 400);
             }
             $group_of_files = Groupoffile::create([
               'file_id' => $filemodel['id'],
               'group_id' => $request->group_id,
             ]);
            return response()->json("File added to public storage successfully", 200);
        } else {
            $error = ValidationException::withMessages([
                'msg' => 'you dont have Access To This File'
            ]);
            throw $error;
        }
      }

      public function deletefile_from_group(Request $request)
      {
           $group_id = Group::find($request->group_id)->id;
           if(!$group_id){
            return response()->json("no such group", 400);
          }
          $file = File::find($request->id);
          if(!$file){
            return response()->json("no such file", 400);
          }
          $file_id = $file->id;
          $fileName = $file->name;
         $groupToFile_id =  Groupoffile::where([['file_id',$file_id],['group_id',$group_id]])->first()->id;
         Groupoffile::destroy($groupToFile_id);
         File::destroy($file_id);
         $lockFilePath = 'public/' . $fileName;
         if (Storage::exists($lockFilePath)) {
             Storage::delete($lockFilePath);
             dump('dddddd.');
         }

      }

public function deleteUserFromGroup($user_id,$group_id)
{


        $user = auth()->user();
        $ownerId = $user->id;
        $userId = $user_id;
        $groupId = $group_id;
        $group = Group::where('id', $groupId)->where('owner', $ownerId)->first();

        if (!$group) {
            return response()->json([
                'message' => 'You are not the owner of this group Only the owner can delete users from the group.',
            ], 403);
        }
        $groupOfUser = Groupofuser::where('group_id', $groupId)->where('user_id', $userId)->first();

        if (!$groupOfUser) {
            return response()->json([
                'message' => 'The user is not a member of the group.',
            ], 404);
        }

        $groupOfFile = Groupoffile::where('group_id', $groupId)
        ->whereHas('file', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->first();


       if ($groupOfFile) {
           return response()->json([
                'message' => 'The user has received files in the group. Remove the files first before deleting the user',
           ], 403);
      }

        $groupOfUser->delete();

        return response()->json([
            'message' => 'User deleted successfully from the group.',
        ], 200);

}
public function deleteGroupIfNoReservedFiles($group_id)
{
    try {
        $group = Group::findOrFail($group_id);

        $hasReservedFiles = File::whereHas('groupoffiles', function ($query) use ($group_id) {
            $query->where('group_id', $group_id);
        })->where('status', 1)->exists();

        if ($hasReservedFiles) {
            return response()->json([
                'message' => 'Cannot delete the group because there are reserved files in it.',
            ], 403);
        }

        $group->delete();

        return response()->json([
            'message' => 'Group deleted successfully.',
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Error deleting group: ' . $e->getMessage());

        return response()->json([
            'message' => 'An error occurred while deleting the group.',
        ], 500);
    }
}

    /*  public function deletefile_from_group(Request $request)
{
    try {

        $request->validate([
          //  'group_id' => 'required|exists:groups,id',
            'file_name' => 'required|string',
        ]);


        $user = auth()->user();
        $ownerId = $user->id;


      //  $group = Group::findOrFail($request->input('file_name'));


        $file = File::where('name', $request->input('file_name'))->firstOrFail();


        if ($file->owner !== $ownerId) {
            return response()->json([
                'message' => 'You are not the owner of this file. Only the owner can delete it from the group.',
            ], 403);
        }


        $groupOfFile = Groupoffile:://where('group_id', $group->id)
            where('file_id', $file->id)
            ->first();


        if (!$groupOfFile) {
            return response()->json([
                'message' => 'File is not associated with the group',
            ], 404);
        }


        $groupOfFile->delete();

        return response()->json([
            'message' => 'File deleted successfully from the group',
        ], 200);
    } catch (\Exception $e) {

        Log::error('Error deleting file from group: ' . $e->getMessage());


        return response()->json([
            'message' => 'An error occurred while deleting the file from the group',
        ], 500);

    }
}*/
}
