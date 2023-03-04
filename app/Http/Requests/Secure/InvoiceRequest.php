<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "user_id" => 'required',
            /*"amount" => 'required|regex:/^\d{1,10}(\.\d{1,2})?$/',
            "description" => 'required|min:3',*/
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
