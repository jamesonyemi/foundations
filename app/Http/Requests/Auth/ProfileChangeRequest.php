<?php namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use App\Models\Employee;
use App\Models\User;
use Sentinel;

class ProfileChangeRequest extends Request
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
        $user = User::find(Sentinel::getUser()->id);
        return [
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'required|unique:users,mobile,' .$user->id,
            'password' => 'required|min:6|confirmed',
            'image' => 'image|mimes:jpeg,jpg,bmp,png,gif|max:3000'
        ];
    }
}
