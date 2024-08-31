<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Http\Resources\LikeResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponseTrait;

    /************************************* basic operations **************************************/
    public function index(){
        $users = User::orderBy('name')->get();
        if ($users->isEmpty()) {
            return $this->apiResponse((object)[], 'No users found', 404);
        }

        return $this->apiResponse(UserResource::collection($users), 'ok', 200);   // Retrieve all users
    }

    public function show($id){
        try {
            $user = User::findOrFail($id);
            return $this->apiResponse(new UserResource($user), 'ok', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse((object)[], 'The users not found', 404);
        }                                                                        // Retrieve a specific user by ID
    }

    /************************************* Relation ship **************************************/
    public function getPosts($id){
        $user = User::find($id);
        if(!$user){
            return $this->apiResponse((object)[], 'The user not found', 404);
        }
        return $this->apiResponse(PostResource::collection($user->posts), 'success posts', 200);
    }                                                                       // One to Many

    public function getComments($id){
        $user = User::find($id);
        if(!$user){
            return $this->apiResponse((object)[], 'The user not found', 404);
        }
        return $this->apiResponse(CommentResource::collection($user->comments), 'success comments', 200);
    }                                                                       // One to Many

    public function getLikes($id){
        $user = User::find($id);
        if(!$user){
            return $this->apiResponse((object)[], 'The user not found', 404);
        }
        return $this->apiResponse(LikeResource::collection($user->likes), 'success likes', 200);
    }                                                                       // One to Many
}
