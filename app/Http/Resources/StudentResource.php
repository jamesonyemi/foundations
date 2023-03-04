<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class StudentResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /*return parent::toArray($request);*/

        return [
            'id' => $this->id,
            'name' => @$this->user->full_name,
            'section' => @$this->section->title,
            'program' => @$this->programme->title,
        ];
    }
}
