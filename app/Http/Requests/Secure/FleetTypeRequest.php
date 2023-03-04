<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;
use App\Models\ProcurementCategory;
use App\Models\Supplier;

class FleetTypeRequest extends Request
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
                    'title' => 'required|min:2',
                    'category_code' => 'unique:procurement_categories,category_code,' . (isset($procurmentCategory->id) ? $procurmentCategory->id : 0),
                ];

            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $procurmentCategory = ProcurementCategory::find($mt[1]);
                }
                return [
                    'title' => 'required|min:2',
                    'category_code' => 'unique:procurement_categories,category_code,' . (isset($procurmentCategory->id) ? $procurmentCategory->id : 0),
                ];
            }
            default:
                break;
        }
    }

}
