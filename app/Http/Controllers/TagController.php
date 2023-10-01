<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    use ApiResponseTrait;

    /************************************* basic operations **************************************/
    public function index(){
        $tags = Tag::get();
        if ($tags->isEmpty()) {
            return $this->apiResponse(null, 'No tags found', 404);
        }

        return $this->apiResponse(TagResource::collection($tags), 'ok', 200);               // Retrieve all tags
    }

    public function show($id){
        try {
            $tag = Tag::findOrFail($id);
            return $this->apiResponse(new TagResource($tag), 'ok', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, 'The tag not found', 404);
        }                                                                                   // Retrieve a specific tag by ID
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if($validator->fails()){
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        $tag = Tag::create($request->all());
        if($tag){
            return $this->apiResponse(new TagResource($tag), 'success insert', 201);
        } else{
            return $this->apiResponse(null, 'Failed to create the tag', 400);
        }                                                                                     // Create a new tag
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        try {
            $tag = Tag::findOrFail($id);
            $tag->update($request->all());
            return $this->apiResponse(new TagResource($tag), 'success update', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, 'The tag not found', 404);
        }                                                                                      // Update an existing tag
    }

    public function destroy($id){
        $tag = Tag::find($id);
        if(!$tag){
            return $this->apiResponse(null, 'The tag not found', 404);
        }

        $tag->delete();
        return $this->apiResponse(null, 'success delete', 204);                                 // Delete a tag
    }


    /************************************* Relation ship **************************************/
    public function getPosts($id){
        $tag = Tag::find($id);
        if(!$tag){
            return $this->apiResponse(null, 'The tag not found', 404);
        }
        return $this->apiResponse(PostResource::collection($tag->posts), 'success posts', 200);
    }                                                                        // Many to Many
}
