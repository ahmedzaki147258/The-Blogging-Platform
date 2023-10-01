<?php

namespace App\Http\Controllers;

use App\Http\Resources\JoinResource;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class JoinController extends Controller
{
    use ApiResponseTrait;

    /**************************************** RightJoin ****************************************/
    public function getPostsAndUsers(){
        $postsAndUsers = Post::RightJoin('users', 'posts.user_id', '=', 'users.id')
            ->select('posts.*', 'users.name as user_name')
            ->get();

        return $this->apiResponse(JoinResource::collection($postsAndUsers), 'ok', 200);
    }

    /**************************************** LeftJoin ****************************************/
    public function getPostsAndImages(){
        $postsAndImages = Post::LeftJoin('images', 'images.post_id', '=', 'posts.id')
            ->select('posts.*', 'images.imagepath as image_path')
            ->get();

        return $this->apiResponse(JoinResource::collection($postsAndImages), 'ok', 200);
    }


    /**************************************** InnerJoin ****************************************/
    public function getCommentsAndPostsAndUsers(){
        $commentsAndPostsAndUsers = Comment::join('posts', 'comments.post_id', '=', 'posts.id')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->select('comments.*', 'posts.title as post_title', 'posts.content as post_content', 'users.name as user_name')
            ->get();

        return $this->apiResponse(JoinResource::collection($commentsAndPostsAndUsers), 'ok', 200);
    }

    public function getLikesAndPostsAndUsers(){
        $likesAndPostsAndUsers = Like::join('posts', 'likes.post_id', '=', 'posts.id')
            ->join('users', 'likes.user_id', '=', 'users.id')
            ->select('likes.*', 'posts.title as post_title', 'posts.content as post_content', 'users.name as user_name')
            ->get();

        return $this->apiResponse(JoinResource::collection($likesAndPostsAndUsers), 'ok', 200);
    }
}
