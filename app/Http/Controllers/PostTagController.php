<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostTagResource;
use App\Models\PostTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostTagController extends Controller
{
    use ApiResponseTrait;

    /************************************* basic operations **************************************/
    public function index(){
        $postTag = PostTag::get();
        if ($postTag->isEmpty()) {
            return $this->apiResponse(null, 'No postTags found', 404);
        }

        return $this->apiResponse(PostTagResource::collection($postTag), 'ok', 200);   // Retrieve all postTags
    }

    public function show($id){
        try {
            $postTag = PostTag::findOrFail($id);
            return $this->apiResponse(new PostTagResource($postTag), 'ok', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, 'The postTag not found', 404);
        }                                                                        // Retrieve a specific postTag by ID
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'tag_id' => 'required|exists:tags,id',
        ]);

        if($validator->fails()){
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        $postTag = PostTag::create($request->all());
        if($postTag){
            return $this->apiResponse(new PostTagResource($postTag), 'success insert', 201);
        } else{
            return $this->apiResponse(null, 'Failed to create the postTag', 400);
        }                                                                           // Create a new postTag
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'tag_id' => 'required|exists:tags,id',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        try {
            $postTag = PostTag::findOrFail($id);
            $postTag->update($request->all());
            return $this->apiResponse(new PostTagResource($postTag), 'success update', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, 'The postTag not found', 404);
        }                                                                            // Update an existing postTag
    }

    public function destroy($id){
        $postTag = PostTag::find($id);
        if(!$postTag){
            return $this->apiResponse(null, 'The postTag not found', 404);
        }

        $postTag->delete();
        return $this->apiResponse(null, 'success delete', 204);                       // Delete a postTag
    }
}
