<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class LegalCaseRequest extends Request
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
            'legal_firm_id' => 'required',
            'suite_number' => 'required',

        ];
    }
}
