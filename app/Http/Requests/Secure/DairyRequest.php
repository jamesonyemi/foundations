<?php namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class DairyRequest extends FormRequest
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
            'title' => 'required|min:3',
            "description" => 'required|min:3',
            "date" => 'required|date_format:"' . Settings::get('date_format') . '"',
            "hour" => 'required|integer',
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
