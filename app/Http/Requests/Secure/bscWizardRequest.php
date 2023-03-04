<?php namespace App\Http\Requests\Secure;

use App\Models\Employee;
use App\Models\User;
use App\Helpers\Settings;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Request;

class bscWizardRequest extends Request
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
            'kras.*' => 'nullable',
            'kras.*.*' => 'nullable',
            'objectives.*' => 'nullable',
            'objectives.*.*' => 'nullable',
            'kpis.*' => 'nullable',
            'kpis.*.*' => 'nullable',
            'activities.*' => 'nullable',
            'activities.*.*' => 'nullable',

        ];
    }
    public function messages()
    {
        return [

        ];
    }

}
