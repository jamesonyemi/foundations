<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;
use App\Models\ProcurementCategory;
use App\Models\ProjectArtisan;
use App\Models\ProjectCategory;
use App\Models\Supplier;

class ProjectArtisanRequest extends Request
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
                    'artisan_code' => 'unique:project_artisans,artisan_code,' . (isset($projectArtisan->id) ? $projectArtisan->id : 0),
                ];

            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $projectArtisan = ProjectArtisan::find($mt[1]);
                }
                return [
                    'title' => 'required|min:2',
                    'artisan_code' => 'unique:project_artisans,artisan_code,' . (isset($projectArtisan->id) ? $projectArtisan->id : 0),
                ];
            }
            default:
                break;
        }
    }

}
