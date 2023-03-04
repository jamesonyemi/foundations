<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Project extends JsonResource
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
            'Country' => isset($this->projectType) ? $this->projectType->title : '',
            'ShipCountry' => $this->title,
            'ShipCity' => $this->title,
            'ShipName' => $this->title,
            'ShipAddress' => $this->title,
            'CompanyEmail' => $this->title,
            'CompanyAgent' => $this->title,
            'CompanyName' => $this->title,
            'Currency' => $this->title,
            'Notes' => $this->title,
            'Department' => $this->title,
            'Website' => $this->title,
            'Latitude' => $this->title,
            'Longitude' => $this->title,
            'ShipDate' => $this->created_at,
            'PaymentDate' => $this->title,
            'TimeZone' => $this->title,
            'TotalPayment' => $this->title,
            'Gender' => 'M',
            'Status' => isset($this->status_id) ? $this->status_id : '',
            'Type' => 1,
            'Actions' => null,
        ];
    }
}
