<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LearningGap extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'RecordID' => $this->id,
            'CompetencyGap' => isset($this->competencyGap) ? $this->competencyGap->title : '',
            'Intervention' => $this->intervention,
            'Deadline' => $this->deadline,
            'Status' => 1,
            'Actions' => null,
        ];
    }
}
