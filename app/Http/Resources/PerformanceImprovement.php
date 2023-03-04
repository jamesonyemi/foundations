<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceImprovement extends JsonResource
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
            'EmployeeID' => $this->employee_id,
            'EmployeeSID' => $this->employee->sID,
            'FullName' => $this->employee->user->full_name,
            'Department' => $this->employee->section->title,
            'EndDate' => $this->end_date,
            'Actions' => null,
        ];
    }
}
