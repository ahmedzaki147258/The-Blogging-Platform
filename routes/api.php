<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostTagController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']); // send Mail
    Route::post('/enterVerifyCode', [AuthController::class, 'enterVerifyCode']);
    Route::post('/changePassword', [AuthController::class, 'changePassword']);
    Route::post('/sendResetCodeEmail', [AuthController::class, 'sendResetCodeEmail']); // send Mail
    Route::post('/resetPassword', [AuthController::class, 'resetPassword']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['jwt.verify'])->group(function () {
    // Users
    Route::get('/users', [UserController::class, 'index']); // Order By
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::get('/user-post/{id}', [UserController::class, 'getPosts']); // 1 to m
    Route::get('/user-like/{id}', [UserController::class, 'getLikes']); // 1 to m
    Route::get('/user-comment/{id}', [UserController::class, 'getComments']); // 1 to m

    // Posts
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/post/{id}', [PostController::class, 'show']);
    Route::post('/post', [PostController::class, 'store']);
    Route::patch('/post/{id}', [PostController::class, 'update']);
    Route::delete('/post/{id}', [PostController::class, 'destroy']);
    Route::post('/posts/search', [PostController::class, 'search']); // Like
    Route::get('/post-user/{id}', [PostController::class, 'getUser']); // n to 1
    Route::get('/post-like/{id}', [PostController::class, 'getLikes']); // 1 to m
    Route::get('/post-comment/{id}', [PostController::class, 'getComments']); // 1 to m
    Route::get('/post-image/{id}', [PostController::class, 'getImages']); // 1 to m
    Route::get('/post-tag/{id}', [PostController::class, 'getTags']); // n to m

    // Comments
    Route::get('/comments', [CommentController::class, 'index']);
    Route::get('/comment/{id}', [CommentController::class, 'show']);
    Route::post('/comment', [CommentController::class, 'store']);
    Route::post('/comment/{id}', [CommentController::class, 'update']);
    Route::delete('/comment/{id}', [CommentController::class, 'destroy']);
    Route::get('/comment-user/{id}', [CommentController::class, 'getUser']); // n to 1
    Route::get('/comment-post/{id}', [CommentController::class, 'getPost']); // n to 1

    // Tags
    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tag/{id}', [TagController::class, 'show']);
    Route::post('/tag', [TagController::class, 'store']);
    Route::post('/tag/{id}', [TagController::class, 'update']);
    Route::delete('/tag/{id}', [TagController::class, 'destroy']);
    Route::get('/tag-post/{id}', [TagController::class, 'getPosts']); // n to m

    // Images
    Route::get('/images', [ImageController::class, 'index']);
    Route::get('/image/{id}', [ImageController::class, 'show']);
    Route::post('/image', [ImageController::class, 'store']);
    Route::post('/image/{id}', [ImageController::class, 'update']);
    Route::delete('/image/{id}', [ImageController::class, 'destroy']);
    Route::get('/image-post/{id}', [ImageController::class, 'getPost']); // n to 1

    // PostTags
    Route::get('/posttags', [PostTagController::class, 'index']);
    Route::get('/posttag/{id}', [PostTagController::class, 'show']);
    Route::post('/posttag', [PostTagController::class, 'store']);
    Route::post('/posttag/{id}', [PostTagController::class, 'update']);
    Route::delete('/posttag/{id}', [PostTagController::class, 'destroy']);

    // Join
    Route::get('/getPostsAndUsers', [JoinController::class, 'getPostsAndUsers']); // RightJoin
    Route::get('/getPostsAndImages', [JoinController::class, 'getPostsAndImages']); // LeftJoin
    Route::get('/getCommentsAndPostsAndUsers', [JoinController::class, 'getCommentsAndPostsAndUsers']); // InnerJoin
    Route::get('/getLikesAndPostsAndUsers', [JoinController::class, 'getLikesAndPostsAndUsers']); // InnerJoin

    // Likes
    Route::post('/toggleLike', [LikeController::class, 'toggleLike']);
    Route::get('/getLikesCount', [LikeController::class, 'getLikesCount']); // Group By, Having
});
