<?php namespace App\Http\Requests\Secure;

use App\Models\Employee;
use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
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
                    'sID' => 'required|min:4|unique:students,sID',
                    'email' => 'required|email|unique:users,email',
                    'email2' => 'email|unique:users,email',
                    /*'birth_date' => 'date_format:"' . Settings::get('date_format') . '"',*/
                    'address' => 'required_if:"' . session('current_company_type'). '",==,3',
                    'section_id' => 'required',
                    'direction_id' => 'required_if:"' . session('current_company_type'). '",==,3',
                    'password' => 'required_if:"' . session('current_company_type'). '",==,3|min:6',
                    'mobile' => 'required_if:"' . session('current_company_type'). '",==,3',
                    'gender' => 'required',
                    'level_id' => 'required',


                ];

            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $student = Employee::find($mt[1]);
                }
                return [
//                    'sID' => 'required_if:"' . session('current_company_type'). '",==,3|min:4',
                    'sID' => 'required|min:6|unique:students,sID,' . (isset($student->id) ? $student->id : 0),
                    'first_name' => 'required|min:3',
                    'last_name' => 'required|min:3',
                    'email' => 'required|email|unique:users,email,' . (isset($student->user->id) ? $student->user->id : 0),
                    'email2' => 'email|unique:users,email,' . (isset($student->user->id) ? $student->user->id : 0),
                    /*'birth_date' => 'date_format:"' . Settings::get('date_format') . '"',*/
                    'address' => 'required_if:"' . session('current_company_type'). '",==,3',
                    'section_id' => 'required',
                    'direction_id' => 'required_if:"' . session('current_company_type'). '",==,3',
                    'password' => 'required_if:"' . session('current_company_type'). '",==,3|min:6',
                    'mobile' => 'required_if:"' . session('current_company_type'). '",==,3',
                    'gender' => 'required',
                    'level_id' => 'required',
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
            'email.required' => 'Email is required!',
            'name.required' => 'Name is required!',
            'password.required' => 'Password is required!'
        ];
    }
}
