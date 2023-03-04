<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Request;

class SectionRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|min:3',
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

    public function messages()
    {
        return [
            'title.required' => 'Title field is required!',
        ];
    }
}
