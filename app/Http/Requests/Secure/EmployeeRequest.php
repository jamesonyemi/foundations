<?php namespace App\Http\Requests\Secure;

use App\Models\Employee;
use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Request;
use Illuminate\Validation\Rules\Password;

class EmployeeRequest extends Request
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
                    'email' => 'required|email|unique:users,email,' . (isset($employee->user->id) ? $employee->user->id : 0),
                    'birth_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
                    /*'address' => 'required_if:"' . session('current_company_type'). '",==,3',*/
                    'password' => [
                        /*'required',*/
                        /*Password::min(8)
                            ->mixedCase()
                            ->letters()
                            ->numbers()
                            ->symbols()
                            ->uncompromised(),*/
                    ],
                    'mobile' => 'required_if:"' . session('current_company_type'). '",==,3',
                    'gender' => 'required',
                    'department_id' => 'required',
                    'position_id' => 'required',
                    'employee_supervisor_id' => 'required',
                    'employee_supervisor_id.*' => 'required',
                    /*'company_id' => 'required',*/

                ];

            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $employee = Employee::find($mt[1]);
                }
                return [
                    /*'sID' => 'required_if:"' . session('current_company_type'). '",==,3|min:4',*/
                    'first_name' => 'required|min:3',
                    'last_name' => 'required|min:3',
                    'email' => 'required|email|unique:users,email,' . (isset($employee->user->id) ? $employee->user->id : 0),
                    'birth_date' => 'required|date_format:"' . Settings::get('date_format') . '"',
                    /*'address' => 'required_if:"' . session('current_company_type'). '",==,3',*/
                    'mobile' => 'required_if:"' . session('current_company_type'). '",==,3',
                    'gender' => 'required',
                    'department_id' => 'required',
                    'position_id' => 'required',
                    /*'employee_supervisor_id' => 'required',*/
                    /*'employee_supervisor_id.*' => 'required',*/
                    /*'country_id' => 'required',*/
                    /*'company_id' => 'required',*/
                    'password' => [
                        /*'nullable',*/
                        /*Password::min(8)
                            ->mixedCase()
                            ->letters()
                            ->numbers()
                            ->symbols()
                            ->uncompromised(),*/
                    ],
                    'employee_supervisor_id' => 'required',
                    'employee_supervisor_id.*' => 'required',
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
            'password.required' => 'Password is required!',
            'section_id.required' => 'Department field is required!',
            'position_id.required' => 'Position field is required!',
            'country_id.required' => 'Country field is required!',
            'employee_supervisor_id.required' => 'Supervisor field is required!',
            'employee_supervisor_id.*.required' => 'Supervisor field is required!'
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
