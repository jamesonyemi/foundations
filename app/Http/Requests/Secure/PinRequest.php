<?php namespace App\Http\Requests\Secure;

use App\Models\Applicant;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class PinRequest extends FormRequest
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
                    'pin' => 'required|min:3',
                ];
            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $applicant = Applicant::find($mt[1]);
                }
                return [
                    'pin' => 'required|min:3',
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
