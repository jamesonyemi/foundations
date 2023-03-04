<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class KpiActivityUpdateRequest extends Request
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
            'kpi_id' => 'required|int',
            'comment' => 'sometimes|required',
            'kt_docs_repeater_basic.*.file' => 'sometimes|nullable',
            'kt_docs_repeater_basic.*.document_title' => 'required_unless:kt_docs_repeater_basic.*.file,null',
        ];
    }
    public function messages()
    {
        return [
            'title.required' => 'Title field is required!',
            'kpi_id.required' => 'KPI is required!',
            'due_date.required' => 'Due date field is required!',
            'comment.required' => 'Kindly provide activity report',
            'kt_docs_repeater_basic.*.document_title.required_unless' => 'Kindly provide document title for the uploaded document(s)',
            'kt_docs_repeater_basic.*.file.required_unless' => 'Kindly upload document(s) for the document title given'
        ];
    }

}
