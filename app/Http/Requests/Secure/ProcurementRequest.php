<?php
namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class ProcurementRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'quantity' => 'required',
            'description' => 'required',
            /*'check_in' => 'required|date_format:"' . Settings::get('date_format') .' '.Settings::get('time_format') . '"',*/

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
