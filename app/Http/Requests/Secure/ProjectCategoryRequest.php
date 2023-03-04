<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;
use App\Models\ProcurementCategory;
use App\Models\ProjectCategory;
use App\Models\Supplier;

class ProjectCategoryRequest extends Request
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
                    'category_code' => 'unique:project_categories,category_code,' . (isset($projectCategory->id) ? $projectCategory->id : 0),
                ];

            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $projectCategory = ProjectCategory::find($mt[1]);
                }
                return [
                    'title' => 'required|min:2',
                    'category_code' => 'unique:project_categories,category_code,' . (isset($projectCategory->id) ? $projectCategory->id : 0),
                ];
            }
            default:
                break;
        }
    }

}
