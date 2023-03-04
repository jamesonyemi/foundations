<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;

class CompanySettingsRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'head_employee_id' => 'required|numeric|min:0|not_in:0',
            'hr_head_employee_id' => 'required|numeric|min:0|not_in:0',
            'procurement_head_employee_id' => 'required|numeric|min:0|not_in:0',
            'it_head_employee_id' => 'required|numeric|min:0|not_in:0',
            'help_desk_admin_employee_id' => 'required|numeric|min:0|not_in:0',
            'send_birthday_message' => 'required',
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
}
