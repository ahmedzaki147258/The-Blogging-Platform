<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CommentController extends Controller
{
    use ApiResponseTrait;

    /************************************* basic operations **************************************/
    public function index(){
        $comments = Comment::get();
        if ($comments->isEmpty()) {
            return $this->apiResponse(null, 'No comments found', 404);
        }

        return $this->apiResponse(CommentResource::collection($comments), 'ok', 200);   // Retrieve all comments
    }

    public function show($id){
        try {
            $comment = Comment::findOrFail($id);
            return $this->apiResponse(new CommentResource($comment), 'ok', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, 'The comment not found', 404);
        }                                                                                  // Retrieve a specific comment by ID
    }

    public function store(Request $request){
        $imagePath = null;
        $validator = Validator::make($request->all(), [
            'content' => 'required',
            'image' => 'image|max:2048',
            'user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
        ]);


        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        if ($request->has('image')) {
            $image = $request->image;
            $name = $request->post_id . '_' . time() . $image->getClientOriginalName();
            $path = public_path('upload');
            $image->move($path, $name);
            $imagePath = 'upload/' . $name;
        }



        // Update the comment data with the image path
        $commentData = $request->all();
        $commentData['imagepath'] = $imagePath;

        $comment = Comment::create($commentData);

        if ($comment) {
            return $this->apiResponse(new CommentResource($comment), 'success insert', 201);
        } else {
            return $this->apiResponse(null, 'Failed to create the comment', 400);
        }                                                                                  // Create a new comment
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'content' => 'required',
            'image' => 'image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        try {
            $comment = Comment::findOrFail($id);

            // Check if the user has changed the image
            $oldImagePath = $comment->imagepath;
            $newImagePath = null;

            if ($request->has('image')) {
                $newImageFile = $request->image;
                $name = $comment->post_id . '_' . time() . $newImageFile->getClientOriginalName();
                $path = public_path('upload');
                $newImageFile->move($path, $name);
                $newImagePath = 'upload/' . $name;
            }

            // Update the comment data
            $commentData = $request->all();
            if($newImagePath){
                $commentData['imagepath'] = $newImagePath ;
            } else if(!$request->has('image')) {
                $commentData['imagepath'] = null ;
            }

            $comment->update($commentData);

            // Delete the old image if it has changed
            if ($oldImagePath && $oldImagePath !== $newImagePath && file_exists(public_path($oldImagePath))) {
                unlink(public_path($oldImagePath));
            }

            return $this->apiResponse(new CommentResource($comment), 'success update', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, 'The comment not found', 404);
        }                                                                                // Update an existing comment
    }


    public function destroy($id){
        $comment = Comment::find($id);
        if (!$comment) {
            return $this->apiResponse(null, 'The comment not found', 404);
        }

        // Check if imagepath exists and delete the image
        if ($comment->imagepath && file_exists(public_path($comment->imagepath))) {
            unlink(public_path($comment->imagepath));
        }

        $comment->delete();
        return $this->apiResponse(null, 'Success delete', 204);                           // Delete a comment
    }



    /************************************* Relation ship **************************************/
    public function getUser($id){
        $comment = Comment::find($id);
        if(!$comment){
            return $this->apiResponse(null, 'The comment not found', 404);
        }
        return $this->apiResponse(new UserResource($comment->user), 'success user', 200);
    }                                                                           // Many to One

    public function getPost($id){
        $comment = Comment::find($id);
        if(!$comment){
            return $this->apiResponse(null, 'The comment not found', 404);
        }
        return $this->apiResponse(new PostResource($comment->post), 'success post', 200);
}                                                                               // Many to One
}
