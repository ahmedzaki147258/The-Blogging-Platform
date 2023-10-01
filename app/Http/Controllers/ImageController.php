<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImageResource;
use App\Http\Resources\PostResource;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    use ApiResponseTrait;

    /************************************* basic operations **************************************/
    public function index(){
        $images = Image::get();
        if ($images->isEmpty()) {
            return $this->apiResponse(null, 'No images found', 404);
        }

        return $this->apiResponse(ImageResource::collection($images), 'ok', 200);   // Retrieve all images
    }

    public function show($id){
        try {
            $image = Image::findOrFail($id);
            return $this->apiResponse(new ImageResource($image), 'ok', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, 'The image not found', 404);
        }                                                                        // Retrieve a specific image by ID
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'images.*' => 'required|image|max:2048', // 'images.*' validates each item in the 'images' array
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $imageFile) {

                // Validate each image
                $validationResult = $this->validImg($imageFile, true, $key + 1);
                if ($validationResult !== null) {
                    return $validationResult;
                }

                // Continue with image processing
                $name = $request->post_id . '-' . time() . $key . $imageFile->getClientOriginalName();
                $path = public_path('upload');
                $imageFile->move($path, $name);
                $imagePath = 'upload/' . $name;

                $imageData = [
                    'post_id' => $request->post_id,
                    'imagepath' => $imagePath,
                ];

                $image = Image::create($imageData);
                $uploadedImages[] = new ImageResource($image);
            }

            return $this->apiResponse($uploadedImages, 'success insert', 201);
        }

        return $this->apiResponse(null, 'No images provided', 400);      // insert multiple images
    }


    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        try {
            $existingImage = Image::find($id);

            // Check if the user has changed the image
            $oldImagePath = $existingImage->imagepath;
            $newImagePath = null;

            if ($request->has('image')) {
                $newImageFile = $request->file('image');
                $name = $existingImage->post_id . '-' . time() . $newImageFile->getClientOriginalName();
                $path = public_path('upload');
                $newImageFile->move($path, $name);
                $newImagePath = 'upload/' . $name;
            }

            // Update the image data
            $imageData = $request->all();
            $imageData['imagepath'] = $newImagePath;

            $existingImage->update($imageData);

            // Delete the old image if it has changed
            if ($oldImagePath && $oldImagePath !== $newImagePath && file_exists(public_path($oldImagePath))) {
                unlink(public_path($oldImagePath));
            }

            return $this->apiResponse(new ImageResource($existingImage), 'success update', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, 'The image not found', 404);
        }                                                                           // change an existing image
    }

    public function destroy($id){
        $image = Image::find($id);
        if (!$image) {
            return $this->apiResponse(null, 'The image not found', 404);
        }

        if ($image->imagepath && file_exists(public_path($image->imagepath))) {
            unlink(public_path($image->imagepath));
        }

        $image->delete();
        return $this->apiResponse(null, 'Success delete', 204);                           // Delete a image
    }


    /************************************* Relation ship **************************************/
    public function getPost($id){
        $image = Image::find($id);
        if(!$image){
            return $this->apiResponse(null, 'The image not found', 404);
        }
        return $this->apiResponse(new PostResource($image->post), 'success post', 200);
    }                                                                   // Many to One
}
