<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePerformance extends JsonResource
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
            'Department' => $this->id,
            'EmployeeID' => $this->sID,
            'FullName' => isset($this->user) ? $this->user->full_name : '',
            'Department' => isset($this->section) ? $this->section->title : '',
            'Position' => isset($this->position) ? $this->position->title : '',
            'TotalScore' => isset($this->total_score) ? $this->total_score : 0,
            'Standing' => isset($this->standing) ? $this->standing : 0,
            'Actions' => null,
        ];
    }
}
