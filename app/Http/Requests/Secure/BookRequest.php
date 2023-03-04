<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class BookRequest extends Request
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
            'title' => 'required|min:3',
            'author' => 'required',
            'year' => 'required|integer',
            'quantity' => 'required|integer|min:0',
            'internal' => 'required|min:3',
            'publisher' => 'required|min:3',
            'version' => 'required',
        ];
    }
}
