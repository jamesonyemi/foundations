<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class UpdateKpiWeightRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'kpi_id.*' => 'required|numeric|',
            'kpi_id.*.*' => 'required|numeric|',
            'kpi_weight.*' => 'required|numeric',
            'kpi_weight.*.*' => 'required|numeric',

        ];
    }


    public function messages()
    {
        return [

            'kpi_id.*.required' => 'KPI not recognised',
            'weight.*' => 'Rating required for all KPIs',
            'kpi_weight.*.required' => 'KPI weight is not set for some of your KPIs',
            'kpi_weight.*.*.required' => 'KPI weight is not set for some of your KPIs'
        ];
    }
}
