<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class ProjectRequest extends Request
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
            'header_report' => 'required|mimes:doc,docx,pdf,xlx,xls,xlsx,csv|max:2048',
            'mel_template' => 'required|mimes:doc,docx,pdf,xlx,xls,xlsx,csv|max:2048',
            'financial_report' => 'required|mimes:doc,docx,pdf,xls,xlx,xlsx,csv|max:2048',
            'nq_work_plan' => 'required|mimes:doc,docx,pdf,xlx,xls,xlsx,csv|max:2048',
            'nq_budget' => 'required|mimes:doc,docx,pdf,xlx,xls,xlsx,csv|max:2048',
            'human_interest' => 'required|mimes:doc,docx,pdf,xls,xlsx,xlx,csv|max:2048',
            'file_file' => 'required|mimes:jpg,jpeg,png|max:2048',
            'video' => 'nullable|mimes:doc,docx,pdf,xlx,xls,csv,jpg,jpeg,mp3|max:2048',
        ];
    }
}
