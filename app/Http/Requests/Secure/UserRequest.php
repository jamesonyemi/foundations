<?php namespace App\Http\Requests\Secure;

use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            case 'POST':{
                    return [
                        'first_name' => 'required|min:3|max:50',
                        'last_name' => 'required|min:3|max:50',
                        'email' => 'required|email|unique:users,email',
                        'mobile' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:users,mobile',
                        'organization' => 'required',
                        /*'password' => 'required|min:6|confirmed',*/

                        /*'g-recaptcha-response' =>  'required|recaptcha',*/
                    ];
            }

            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $user = User::find($mt[1]);
                }

                return [
                    'first_name' => 'required|min:3|max:50|alpha',
                    'last_name' => 'required|min:3|max:50|alpha',
                    'email' => 'required|email|unique:users,email,' .$user->id,
                    'mobile' => 'required|unique:users,mobile,' .$user->id,
                    'password' => 'required|min:6|confirmed',
                    /*'g-recaptcha-response' =>  'required|recaptcha',*/
                ];
            }
            default:
                break;
        }

        return [

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
