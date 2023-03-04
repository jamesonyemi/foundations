<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;
use App\Models\Fleet;
use App\Models\Supplier;

class FleetRequest extends Request
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
                    'fleet_make_id' => 'required',
                    'fleet_type_id' => 'required',
                    'fleet_category_id' => 'required',
                    'fleet_number' => 'unique:fleet,fleet_number,' . (isset($fleet->id) ? $fleet->id : 0),
                    'chassis_number' => 'unique:fleet,chassis_number,' . (isset($fleet->id) ? $fleet->id : 0),
                ];
            }

            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $fleet = Fleet::find($mt[1]);
                }
                return [
                    'fleet_make_id' => 'required',
                    'fleet_type_id' => 'required',
                    'fleet_category_id' => 'required',
                    'fleet_number' => 'unique:fleet,fleet_number,' . (isset($fleet->id) ? $fleet->id : 0),
                    'chassis_number' => 'unique:fleet,chassis_number,' . (isset($fleet->id) ? $fleet->id : 0),
                ];
            }
            default:
                break;
        }
    }

}
