<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->user?->full_name,
            'birth date'    => $this->birth_date,
            'gender'        => $this->gender,
            'phone number'  => $this->phone_number,
            'address'       => $this->address,
            'picture'       => $this->picture_url,
            'hiring date'   => $this->hiring_date,
            'department' => $this->user?->department?->name,
            'manager' => User::role('manager')
                ->where('dep_id', $this->user->dep_id)
                ->first()?->full_name ?? 'Not assigned',
        ];
    }
}
