<?php namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class StaffLeaveRecordRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "employee_id" => 'required|integer',
            "staff_leave_type_id" => 'required|integer',
            "start_date" => 'required|date_format:"' . Settings::get('date_format') . '"',
            "end_date" => 'required|date_format:"' . Settings::get('date_format') . '"',
            "days" => 'required',
            "reliever_employee_id" => 'required',
            "return_date" => 'required',
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
