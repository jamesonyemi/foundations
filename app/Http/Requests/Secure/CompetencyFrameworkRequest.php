<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Request;

class CompetencyFrameworkRequest extends Request
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
            'competency_matrix_id' => 'required',
            'competency_matrix_id.*' => 'required',
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
            'competency_matrix_id.required' => 'Competency Matrix field is required!',
            'competency_matrix_id.*' => 'Competency Matrix field is required!',
        ];
    }
}
