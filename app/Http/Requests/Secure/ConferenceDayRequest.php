<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;
use Efriandika\LaravelSettings\Facades\Settings;

class ConferenceDayRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'    => 'required|min:3',
            'day_name'    => 'required|min:3',
            'day_date'    => 'required|date_format:"' . Settings::get('date_format') . '"',
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
