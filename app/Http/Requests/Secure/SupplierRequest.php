<?php

namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Request;
use App\Models\Employee;
use App\Models\Supplier;
use Illuminate\Validation\Rules\Password;

class SupplierRequest extends Request
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
        switch ($this->method()) {
            case 'GET':
            case 'DELETE': {
                return [];
            }
            case 'POST': {


                return [
                    'title' => 'required|min:3|unique:suppliers,title,' . (isset($supplier->id) ? $supplier->id : 0),
                    'email_address' => 'required|email',
                    'phone_number' => 'required',
                    'procurement_category_id.*' => 'required',
                    'procurement_category_id.*.*' => 'required',
                    'contract_start_date' => 'required|date',
                    'contract_end_date' => 'required|date',
                    'tin_number' => 'required|unique:suppliers,tin_number,' . (isset($supplier->id) ? $supplier->id : 0),

                ];

            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $supplier = Supplier::find($mt[1]);
                }
            return [
                'title' => 'required|min:3|unique:suppliers,title,' . (isset($supplier->id) ? $supplier->id : 0),
                'email_address' => 'required|email',
                'phone_number' => 'required',
                'procurement_category_id.*' => 'required',
                'procurement_category_id.*.*' => 'required',
                'contract_start_date' => 'required|date',
                'contract_end_date' => 'required|date',
                'tin_number' => 'required|unique:suppliers,tin_number,' . (isset($supplier->id) ? $supplier->id : 0),

            ];
            }
            default:
                break;
        }
    }

}
