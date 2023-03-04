<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PerformanceEmployeeResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public $collects = \App\Http\Resources\EmployeePerformance::class;

    public function toArray($request)
    {
        return [
            'meta' => [
                'page' => 1,
                'pages' => 1,
                'perpage' => 5,
                'total' => 0,
                'sort' => -1,
                'field' => 'RecordID',
            ],
            'data' => $this->collection,
        ];
    }
}
