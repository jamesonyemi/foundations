<?php namespace App\Http\Requests\Secure;

use App\Models\Applicant;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class ApplicantInfoRequest extends FormRequest
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
                    'birth_date' => 'date_format:"' . Settings::get('date_format') . '"',
                    'address' => 'required',
                    'section_id' => 'required',
                    'password' => 'required|min:6',
                    'mobile' => 'required',
                    'gender' => 'required',
                ];
            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $applicant = Applicant::find($mt[1]);
                }
                return [
                    'first_name' => 'required|min:3',
                    'last_name' => 'required|min:3',
                    'birth_date' => 'date_format:"' . Settings::get('date_format') . '"',
                    'address' => 'required',
                    'section_id' => 'required',
                    'password' => 'min:6',
                    'mobile' => 'required',
                    'gender' => 'required',
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
