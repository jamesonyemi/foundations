<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompetencyFramework extends JsonResource
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
            'OrderID' => $this->id,
            'Country' => isset($this->kpi) ? $this->kpi->title : '',
            'ShipCountry' => $this->comment,
            'ShipCity' => $this->comment,
            'ShipName' => $this->comment,
            'ShipAddress' => $this->comment,
            'CompanyEmail' => $this->comment,
            'CompanyAgent' => $this->comment,
            'CompanyName' => $this->comment,
            'Currency' => $this->comment,
            'Notes' => $this->comment,
            'Department' => $this->comment,
            'Website' => $this->comment,
            'Latitude' => $this->comment,
            'Longitude' => $this->comment,
            'ShipDate' => $this->created_at,
            'PaymentDate' => $this->comment,
            'TimeZone' => $this->comment,
            'TotalPayment' => $this->comment,
            'Gender' => 'M',
            'Status' => 5,
            'Type' => 1,
            'Actions' => null,
        ];
    }
}
