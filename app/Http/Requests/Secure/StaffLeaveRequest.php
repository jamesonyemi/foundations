<?php namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class StaffLeaveRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "staff_leave_type_id" => 'required|integer',
            "start_date" => 'required|date_format:"' . Settings::get('date_format') . '"',
            "end_date" => 'required|date_format:"' . Settings::get('date_format') . '"',
            "days" => 'required|numeric|min:0|not_in:0',
            "reliever_employee_id" => 'required',
            "return_date" =>  'required|date_format:"' . Settings::get('date_format') . '"',
            'kt_docs_repeater_basic.*.file' => 'sometimes|nullable',
            'kt_docs_repeater_basic.*.document_title' => 'required_unless:kt_docs_repeater_basic.*.file,null',
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
