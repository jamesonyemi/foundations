<?php
namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'employee_request_category_id' => 'required',
            'approvals_employee_id.*' => 'required',
            'copy_employee_id.*' => 'nullable',
            'request_body' => 'nullable',
            'employee_id' => 'nullable',
            'kt_docs_repeater_basic.*.file' => 'sometimes|nullable',
            'kt_docs_repeater_basic.*.document_title' => 'required_unless:kt_docs_repeater_basic.*.file,null',
            /*'dead_line' => 'required|date_format:"' . Settings::get('date_format') .' '.Settings::get('date_format') . '"',*/

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
            'title.required' => 'Title field is required!',
            'employee_request_category_id.required' => 'Request Category is required!',
            /*'weight.required' => 'Weight field is required!',*/
            'due_date.required' => 'Due date field is required!',
            'kt_docs_repeater_basic.*.document_title.required_unless' => 'Kindly provide document title for the uploaded document(s)',
            'kt_docs_repeater_basic.*.file.required_unless' => 'Kindly upload document(s) for the document title given'
        ];
    }
}
