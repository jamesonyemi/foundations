<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class CreateNewSections extends Request
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
            'select_school_year_id' => 'required',
            'select_company_id' => 'required',
            'section_id' => 'required',
            'section_name' => 'required',
        ];
    }
}
