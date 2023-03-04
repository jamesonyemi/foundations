<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class QualificationFrameworkRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'section_id' => 'required|integer',
            'position_id' => 'required|integer',
            'qualification_id' => 'required',
            'qualification_id.*' => 'required',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'section_id.required' => 'Department field is required!',
            'position_id.required' => 'Position field is required!',
            'qualification_id.required' => 'Qualifications field is required!',
            'qualification_id.*' => 'Qualifications field is required!',
        ];
    }
}
