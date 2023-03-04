<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;
use App\Models\ProcurementCategory;
use App\Models\ProcurementItem;

class ProcurementItemRequest extends Request
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
                    'title' => 'required|min:2|unique:procurement_items,title,' . (isset($procurmentItem->id) ? $procurmentItem->id : 0),
                    'procurement_category_id' => 'required|integer',
                ];

            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $procurmentItem = ProcurementItem::find($mt[1]);
                }
            return [
                'title' => 'required|min:2|unique:procurement_items,title,' . (isset($procurmentItem->id) ? $procurmentItem->id : 0),
                'procurement_category_id' => 'required|integer',
            ];

        }
            default:
                break;
        }
    }


}
