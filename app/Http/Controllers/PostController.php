<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\LikeResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\UserResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    use ApiResponseTrait;

    /************************************* basic operations **************************************/
    public function index(){
        $posts = Post::get();
        if ($posts->isEmpty()) {
            return $this->apiResponse((object)[], 'No posts found', 404);
        }

        return $this->apiResponse(PostResource::collection($posts), 'ok', 200);   // Retrieve all posts
    }

    public function show($id){
        try {
            $post = Post::findOrFail($id);
            return $this->apiResponse(new PostResource($post), 'ok', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse((object)[], 'The post not found', 404);
        }                                                                        // Retrieve a specific post by ID
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'content' => 'required',
            'user_id' => 'required|exists:users,id',
        ]);
        if($validator->fails()){
            return $this->apiResponse((object)[], $validator->errors(), 400);
        }

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => $request->user_id,
        ]);
        if($post){
            return $this->apiResponse(new PostResource($post), 'success insert', 201);
        } else{
            return $this->apiResponse((object)[], 'Failed to create the post', 400);
        }                                                                           // Create a new post
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|max:255',
            'content' => 'nullable',
        ]);
        if($validator->fails()){
            return $this->apiResponse((object)[], $validator->errors(), 400);
        }

        try {
            $post = Post::findOrFail($id);
            $post->update($request->only(['title', 'content']));
            return $this->apiResponse(new PostResource($post), 'success update', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse((object)[], 'The post not found', 404);
        }                                                                            // Update an existing post
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->apiResponse((object)[], 'The post not found', 404);
        }

        $directory = public_path('upload');
        $files = scandir($directory); // get all images

        $deletedFiles = [];
        $notFoundFiles = [];

        foreach ($files as $filename) {
            if (strpos($filename, $id . '-') === 0 || strpos($filename, $id . '_') === 0) {
                $filePath = $directory . DIRECTORY_SEPARATOR . $filename;

                if (file_exists($filePath)) {
                    unlink($filePath);
                    $deletedFiles[] = $filename;
                } else {
                    $notFoundFiles[] = $filename;
                }
            }
        }

        // Additional cleanup or actions related to the post...

        $post->delete();

        $responseMessage = 'Success delete';
        if (!empty($deletedFiles)) {
            $responseMessage .= '. Deleted: ' . implode(', ', $deletedFiles);
        }
        if (!empty($notFoundFiles)) {
            $responseMessage .= '. Not found: ' . implode(', ', $notFoundFiles);
        }

        return $this->apiResponse((object)[], $responseMessage, 204);             // Delete a post
    }



    /**************************************** Search *****************************************/
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        if (empty($keyword)) {
            return $this->apiResponse((object)[], 'Keyword is empty', 400);
        }

        $posts = Post::where('title', 'like', "%$keyword%")
                      ->orWhere('content', 'like', "%$keyword%")
                      ->get();

        if ($posts->isEmpty()) {
            return $this->apiResponse((object)[], 'No posts found for the given ' . $keyword, 404);
        }
        return $this->apiResponse($posts, 'success search', 200);
    }


    /************************************* Relation ship **************************************/
    public function getUser($id){
        $post = Post::find($id);
        if(!$post){
            return $this->apiResponse((object)[], 'The post not found', 404);
        }
        return $this->apiResponse(new UserResource($post->user), 'success user', 200);
    }                                                                       // Many to One

    public function getComments($id){
        $post = Post::find($id);
        if(!$post){
            return $this->apiResponse((object)[], 'The post not found', 404);
        }
        return $this->apiResponse(CommentResource::collection($post->comments), 'success comments', 200);
}                                                                            // One to Many

    public function getTags($id){
        $post = Post::find($id);
        if(!$post){
            return $this->apiResponse((object)[], 'The post not found', 404);
        }
        return $this->apiResponse(TagResource::collection($post->tags), 'success tags', 200);
    }                                                                       // Many to Many

    public function getImages($id){
        $post = Post::find($id);
        if(!$post){
            return $this->apiResponse((object)[], 'The post not found', 404);
        }
        return $this->apiResponse(ImageResource::collection($post->images), 'success images', 200);
}                                                                           // One to Many

    public function getLikes($id){
        $post = Post::find($id);
        if(!$post){
            return $this->apiResponse((object)[], 'The post not found', 404);
        }
        return $this->apiResponse(LikeResource::collection($post->likes), 'success likes', 200);
    }                                                                       // One to Many

}
