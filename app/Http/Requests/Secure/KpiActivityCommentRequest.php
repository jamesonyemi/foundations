<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class KpiActivityCommentRequest extends BaseFormRequest
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
            'employee_kpi_activity_id' => 'required',
            'activityComment' => 'required',

        ];
    }
}
