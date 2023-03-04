<?php namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class MarkRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mark_type_id' => 'required|integer',
            'mark_value_id' => ($this->mark_percent == '')?'required':'',
            'mark_percent' => ($this->mark_value_id == '' && $this->mark_percent == '')?'required':'',
            'student_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'date' => 'required|date_format:"' . Settings::get('date_format') . '"'
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
