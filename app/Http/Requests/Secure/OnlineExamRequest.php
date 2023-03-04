<?php namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class OnlineExamRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "subject_id" => 'required|integer',
            'title' => 'required',
            "description" => 'required',
            "date_start" => 'required|date_format:"' . Settings::get('date_format') . '"',
            "date_end" => 'required|date_format:"' . Settings::get('date_format') . '"',
            "exam_time" => 'required|integer'
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
