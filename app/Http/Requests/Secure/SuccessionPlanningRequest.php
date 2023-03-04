<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Request;

class SuccessionPlanningRequest extends Request
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
            'employee_id' => 'required|numeric|min:0|not_in:0',
            'ready_year_id' => 'required',
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
            'employee.required' => 'Employee field is required!',
            'ready_year_id.required' => 'Ready Year field is required!',
        ];
    }
}
