<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Settings;

class DeferralRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "student_id" => 'required',
            'expected_return_date' => 'required|after:today|date_format:"' . Settings::get('date_format') . '"',
            "description" => 'required|min:3',
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
