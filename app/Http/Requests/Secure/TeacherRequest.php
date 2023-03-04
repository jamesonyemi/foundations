<?php namespace App\Http\Requests\Secure;

use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Sentinel;

class TeacherRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {

        switch ($this->method()) {
            case 'GET':
            case 'DELETE': {
                return [];
            }
            case 'POST': {
                $user_exists = User::where('email', $request->get('email'))->first();
                $is_user_with_role = false;
                if (isset($user_exists)) {
                    $user = Sentinel::findById($user_exists->id);
                    $is_user_with_role = $user->inRole('teacher');
                }

                return [
                    'first_name' => 'required|min:3',
                    'last_name' => 'required|min:3',
                    'email' => 'required|email|unique:users,email' . (($is_user_with_role) ? ',' . $user_exists->id : ""),
                    /*'birth_date' => 'date_format:"' . Settings::get('date_format') . '"',*/
                    'address' => 'required',
                    'mobile' => 'required',
                    'password' => (!isset($user_exists->id)) ? 'required|min:6' : "",
                ];
            }
            case 'PUT':
            case 'PATCH': {
                if ($request->segment(2) != "") {
                    $user = User::find($request->segment(2));
                }
                return [
                    'first_name' => 'required|min:3',
                    'last_name' => 'required|min:3',
                    'email' => 'required|email|unique:users,email,' . $user->id,
                    /*'birth_date' => 'date_format:"' . Settings::get('date_format') . '"',*/
                    'address' => 'required',
                    'mobile' => 'required',
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
