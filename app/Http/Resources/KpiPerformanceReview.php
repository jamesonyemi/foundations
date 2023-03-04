<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KpiPerformanceReview extends JsonResource
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
            'OrderID' => isset($this->kpi) ? $this->kpi->title : '',
            'Department' => @isset($this->kpi) ? @$this->kpi->kpiObjective->title : '',
            'Kpi' => isset($this->kpi) ? $this->kpi->title : '',
            'CompanyEmail' => isset($this->kpi) ? $this->kpi->title : '',
            'CompanyAgent' => isset($this->kpi) ? $this->kpi->title : '',
            'CompanyName' => isset($this->kpi) ? $this->kpi->title : '',
            'Currency' => isset($this->kpi) ? $this->kpi->title : '',
            'Notes' => isset($this->kpi) ? $this->kpi->title : '',
            'Department' => isset($this->kpi) ? $this->kpi->title : '',
            'Website' => isset($this->kpi) ? $this->kpi->title : '',
            'Timeline' => isset($this->kpiTimeline) ? $this->kpiTimeline->title : '',
            'Weight' => isset($this->kpi) ? $this->kpi->weight : '',
            'SRating' => isset($this->kpi) ? $this->self_rating : '',
            'MRating' => isset($this->kpi) ? $this->agreed_rating : '',
            'Gender' => 'M',
            'Status' => 5,
            'Type' => 1,
            'Actions' => null,
        ];
    }
}
