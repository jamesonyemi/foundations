<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class PayeSetupRequest extends Request
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
            'pr_tax_law_id' => 'required',
            'paye_tier' => 'required',
            'rate' => 'required',

        ];
    }
}
