<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class KpiActivityRequest2 extends Request
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
            'title' => 'required|min:3',
            'kpi_id' => 'required|int',
            'due_date' => 'required|date',

        ];
    }
    public function messages()
    {
        return [
            'title.required' => 'Title field is required!',
            'kpi_id.required' => 'KPI is required!',
            /*'weight.required' => 'Weight field is required!',*/
            'due_date.required' => 'Due date field is required!'
        ];
    }

}
