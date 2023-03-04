<?php namespace App\Http\Requests\Secure;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Settings;

class JournalRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account_id'  => 'required',
            'narration'  => 'required|min:5',
            'amount'  => 'required',
            'journal_date' => 'date_format:"' . Settings::get('date_format') . '"',
        ];
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
