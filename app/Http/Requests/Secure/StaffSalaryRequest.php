<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;
use App\Helpers\Settings;

class StaffSalaryRequest extends Request
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
            'date_start' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'date_end' => 'date_format:"' . Settings::get('date_format') . '"',
            'company_id' => 'required',
            'price' => 'required',
        ];
    }
}
