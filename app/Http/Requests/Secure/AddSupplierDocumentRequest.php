<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class AddSupplierDocumentRequest extends Request
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
            'supplier_id' => 'required|integer',
            'document_title' => 'required',
            'file' => 'required',
        ];
    }
}
