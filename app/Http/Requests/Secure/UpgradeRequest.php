<?php namespace App\Http\Requests\Secure;

use App\Models\Employee;
use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;

class UpgradeRequest extends FormRequest
{

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
                    'section_id' => 'required',
                    'direction_id' => 'required',
                    'level_of_adm' => 'required',
                    'level_id' => 'required',
                    'session_id' => 'required',
                    'country_id' => 'required',
                    'campus_id' => 'required',
                    'entry_mode_id' => 'required',
                    'intake_period_id' => 'required',



                ];
            }
            case 'PUT':
            case 'PATCH': {
                if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                    $student = Employee::find($mt[1]);
                }
                return [
                    'section_id' => 'required',
                    'direction_id' => 'required',
                    'level_of_adm' => 'required',
                    'level_id' => 'required',
                    'session_id' => 'required',
                    'country_id' => 'required',
                    'campus_id' => 'required',
                    'entry_mode_id' => 'required',
                    'intake_period_id' => 'required',
                ];
            }
            default:
                break;
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
