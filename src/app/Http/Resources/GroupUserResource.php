<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property mixed id
 * @property mixed name
 * @property mixed role
 * @property mixed status
 * @property mixed groupId
 * @property mixed username
 * @property mixed avatar_path
 */
class GroupUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'status' => $this->status,
            'group_id' => $this->groupId,
            'username' => $this->username,
            'avatar_url' => $this->avatar_path ? Storage::url($this->avatar_path) : '/image/no-avatar.png',
        ];
    }
}
