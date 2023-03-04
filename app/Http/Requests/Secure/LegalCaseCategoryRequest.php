<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;
use App\Models\LegalCaseCategory;
use App\Models\ProcurementCategory;
use App\Models\Supplier;

class LegalCaseCategoryRequest extends Request
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
                ];

            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $legalCaseCategory = LegalCaseCategory::find($mt[1]);
                }
                return [
                    'title' => 'required|min:2',
                ];
            }
            default:
                break;
        }
    }

}
