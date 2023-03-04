<?php namespace App\Http\Requests\Secure;

use App\Models\Employee;
use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class TeacherImportRequest extends FormRequest
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
            case 'DELETE':
            case 'PUT':
            case 'PATCH': {
                return [];
            }
            case 'POST': {
                return [
                    'first_name' => 'required|min:3',
                    'last_name' => 'required|min:3',
                    'email' => 'required|email',
                    'address' => 'required',
                    'mobile' => 'required',
                    'password' => 'required|min:6'
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
