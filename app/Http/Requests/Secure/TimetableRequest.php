<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;

class TimetableRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'teacher_subject_id' => 'required|integer',
            'week_day' => 'required|digits_between:1,5',
            'hour' => 'required|digits_between:1,7',
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
