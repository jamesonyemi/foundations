<?php namespace App\Http\Requests\Secure;

use App\Models\Employee;
use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Request;

class employeeDataWizardRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'nullable',
            'middle_name' => 'nullable',
            'last_name' => 'nullable',
            'maiden_name' => 'nullable',
            'height' => 'nullable',
            'weight' => 'nullable',
            'address' => 'nullable',
            'address_line2' => 'nullable',
            'mobile2' => 'nullable',
            'gender' => 'nullable',
            'birth_date' => 'nullable',
            'birth_city' => 'nullable',
            'home_town' => 'nullable',
            'spouse_name' => 'nullable',
            'mother_name' => 'nullable',
            'father_name' => 'nullable',
            'social_security_number' => 'nullable',
            'tin_number' => 'nullable',
            'bank_account_number' => 'nullable',
            'bank_id' => 'nullable',
            'bank_branch_id' => 'nullable',
            'bank_branch' => 'nullable',
            'country_id' => 'nullable',
            'marital_status_id' => 'nullable',
            'disability' => 'nullable',
            'passport_number' => 'nullable',
            'driver_license' => 'nullable',
            'driver_license_number' => 'nullable',
            'driver_license_place_issue' => 'nullable',

            'Echildrens.*' => 'nullable',
            'Echildrens.*.*' => 'nullable',
            'jospong_employs.*' => 'nullable',
            'jospong_employs.*.*' => 'nullable',
            'jospong_relative_employs.*' => 'nullable',
            'jospong_relative_employs.*.*' => 'nullable',
        ];
    }
    public function messages()
    {
        return [

        ];
    }

}
