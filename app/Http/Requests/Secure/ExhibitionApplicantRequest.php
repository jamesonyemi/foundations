<?php namespace App\Http\Requests\Secure;

use App\Models\Employee;
use App\Models\ExhibitionApplicant;
use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Request;
use Illuminate\Validation\Rules\Password;

class ExhibitionApplicantRequest extends Request
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
                    'first_name' => 'required|min:3',
                    'last_name' => 'required|min:3',
                    'email_address' => 'required|email|unique:exhibition_applicants,email_address,' . (isset($exhibitionApplicant->id) ? $exhibitionApplicant->id : 0),
                    'country_id' => 'required',

                ];

            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $exhibitionApplicant = ExhibitionApplicant::find($mt[1]);
                }
                return [
                    'first_name' => 'required|min:3',
                    'last_name' => 'required|min:3',
                    'email_address' => 'required|email|unique:exhibition_applicants,email_address,' . (isset($exhibitionApplicant->id) ? $exhibitionApplicant->id : 0),
                    'country_id' => 'required',
                    'interest_id' => 'required',
                    'interest_id.*' => 'required',
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

    public function messages()
    {
        return [
            'email_address.required' => 'Email is required!',
            'first_name.required' => 'Name is required!',
            'last_name.required' => 'Password is required!',
            'country_id.required' => 'Country field is required!',
            'interest_id.*.required' => 'Kindly select a field of interest'
        ];
    }

    /**
     *  Filters to be applied to the input.
     *
     * @return array
     */
/*    public function filters()
    {
        return [
            'email' => 'trim|lowercase',
            'first_name' => 'trim|capitalize|escape',
            'last_name' => 'trim|capitalize|escape'
        ];
    }*/
}
