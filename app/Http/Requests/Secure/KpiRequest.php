<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class KpiRequest extends Request
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
            'title' => 'sometimes|required',
            'kras.*' => 'nullable',
            'kras.*.*' => 'nullable',
            'kpi_weight' => 'sometimes|required|numeric|min:0|not_in:0',
            'kpi_weight.*' => 'required|numeric|min:0|not_in:0',
            'kpi_weight.*.*' => 'required|numeric|min:0|not_in:0',
            'kpi_objective_id.*' => 'required|numeric|gt:0',
            'kpi_objective_id.*.*' => 'required|numeric|gt:0',
            'kpis.*' => 'required',
            'kpis.*.*' => 'required',
            'timeline.*' => 'required',
            'supervisor_employee_id' => 'sometimes|required',
            'supervisor_employee_id.*' => 'required',
            'supervisor_employee_id.*.*' => 'required',
            'activities.*' => 'nullable',
            'activities.*.*' => 'nullable',

        ];
    }
    public function messages()
    {
        return [

        ];
    }

}
