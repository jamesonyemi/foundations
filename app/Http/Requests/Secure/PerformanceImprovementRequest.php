<?php

namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Request;

class PerformanceImprovementRequest extends BaseFormRequest
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
            'employee_id' => 'required',
            'kpis' => 'required',
            'end_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'supervisor_employee_id' => 'required',
            'employee_id.*' => 'required',

        ];
    }
}
