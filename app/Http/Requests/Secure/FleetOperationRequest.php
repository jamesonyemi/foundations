<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;
use App\Models\ProcurementCategory;
use App\Models\Supplier;

class FleetOperationRequest extends Request
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
            'fleet_id' => 'required',
            'driver_employee_id' => 'required',
            'fleet_status' => 'required',

        ];
    }

}
