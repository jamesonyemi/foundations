<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Request;

class JobDescriptionRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'department_id' => 'required|integer',
            'position_id' => 'required|integer',
            'description' => 'required',

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
            'description.required' => 'Description field is required!',
        ];
    }
}
