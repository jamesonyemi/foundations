<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Settings;

class ApplicantSchoolRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|min:3',
            /*'start_date' => 'date_format:"' . Settings::get('date_format') . '"',
            'end_date' => 'date_format:"' . Settings::get('date_format') . '"',*/
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
