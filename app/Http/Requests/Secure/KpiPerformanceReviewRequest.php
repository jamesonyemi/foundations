<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class KpiPerformanceReviewRequest extends Request
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
            'weight.*' => 'required|numeric|min:0|max:5',
            'weight.*.*' => 'required|numeric|min:0|max:5',
            'kpi_id.*' => 'required|numeric|',
            'kpi_id.*.*' => 'required|numeric|',
            /*'perspectiveWeight' => 'required|numeric|gt:0',*/
            'kpi_weight.*' => 'required|numeric',
            'kpi_weight.*.*' => 'required|numeric',

        ];
    }


    public function messages()
    {
        return [
            /*'perspectiveWeight.gt' => 'Please Set your perspective weights',*/
            /*'perspectiveWeight.required' => 'Please Set your perspective weights',*/
            'kpi_id.*.required' => 'KPI not recognised',
            'weight.*' => 'Rating required for all KPIs',
            'weight.*.max' => 'Rating should not be more than 5 for all KPIs',
            'weight.*.required' => 'Rating required for all KPIs',
            'kpi_weight.*.required' => 'KPI weight is not set for some of your KPIs',
            'kpi_weight.*.*.required' => 'KPI weight is not set for some of your KPIs'
        ];
    }
}
