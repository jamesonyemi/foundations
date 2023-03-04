<?php
namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class TimetablePeriodRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_at' => 'required|date_format:"' . Settings::get('time_format') . '"',
            'end_at' => 'required|date_format:"' . Settings::get('time_format') . '"',
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
