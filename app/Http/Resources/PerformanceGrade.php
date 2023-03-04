<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceGrade extends JsonResource
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
            'Title' => $this->title,
            'MinValue' => $this->min_score,
            'MaxValue' => $this->max_score,
            'Grade' => $this->grade,
            'Actions' => null,
        ];
    }
}
