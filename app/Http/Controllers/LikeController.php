<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    use ApiResponseTrait;

    public function toggleLike(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        $existingLike = Like::where('user_id', $request->user_id)->where('post_id', $request->post_id)->first();

        if ($existingLike) {
            $existingLike->delete();
            $message = 'Like removed successfully.';
        } else {
            Like::create(['user_id' => $request->user_id, 'post_id' => $request->post_id]);
            $message = 'Like added successfully.';
        }
        return $this->apiResponse(null, $message, 200);
    }

    public function getLikesCount(){
        $likesCount = Like::selectRaw('post_id, COUNT(*) as count')
            ->groupBy('post_id')
            ->having('count', '>', 0)
            ->get();

        return $this->apiResponse(['likes_count' => $likesCount], 'success count', 200);
    }

}
