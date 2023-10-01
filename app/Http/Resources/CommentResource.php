<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id'=> $this->id,
            'content'=> $this->content,
            'Image path'=> $this->imagepath,
            'User Id'=> $this->user_id,
            'Post Id'=> $this->post_id,
        ];
    }
}
