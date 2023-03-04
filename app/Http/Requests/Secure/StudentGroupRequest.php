<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;

class StudentGroupRequest extends FormRequest
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
                    'direction_id' => 'required|integer',
                    'title' => 'required|min:3',
                    'class' => 'required|integer'
                ];
            }
            case 'PUT':
            case 'PATCH': {
                return [
                    'title' => 'required|min:3',
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
