<?php

namespace App\Http\Resources\Evaluation;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeNoteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'type'    => $this->type,
            'content' => $this->content,

            'author' => $this->whenLoaded('author', fn () => $this->author ? [
                'id'   => $this->author->id,
                'name' => $this->author->full_name,
            ] : null),

            'created_at' => $this->created_at,


        ];
    }
}
