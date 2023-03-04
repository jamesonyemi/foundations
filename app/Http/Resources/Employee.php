<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Employee extends JsonResource
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
            'CompetencyMatrix' => isset($this->competencies) ? $this->competencies->count().'/'.$this->expected_competencies : 0,
            'CompetencyScore' => isset($this->competency_score) ? @$this->competency_score : 0,
            'QualificationMatrix' => isset($this->qualifications) ? $this->qualifications->count().'/'.$this->expected_qualifications : 0,
            'Gender' => isset($this->user) ? $this->user->gender : '',
            'Status' => $this->status,
            'Actions' => null,
        ];
    }
}
