<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Perspectives extends JsonResource
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
            'OrderID' => $this->title,
            'Country' => $this->title,
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
            'ShipDate' => $this->title,
            'PaymentDate' => $this->title,
            'TimeZone' => $this->title,
            'TotalPayment' => $this->title,
            'Gender' => 'M',
            'Status' => 5,
            'Type' => 1,
            'Actions' => null,
        ];
    }
}
