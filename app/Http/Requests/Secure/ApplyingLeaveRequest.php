<?php
namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class ApplyingLeaveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'student_id' => 'required',
            'title' => "required",
            'date' => 'required|date_format:"' . Settings::get('date_format') . '"',
            'description' => "required"
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
