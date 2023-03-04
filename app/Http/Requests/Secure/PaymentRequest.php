<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'GET':
            case 'DELETE': {
                return [];
            }
            case 'POST': {
                return [
                    "student_id" => "required",
                    "amount" => "required",
                ];
            }
            case 'PUT':
            case 'PATCH': {
                return [
                    /*"payment_method" => "required",*/
                    "amount" => "required",
                ];
            }
            default:
                break;
        }
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
