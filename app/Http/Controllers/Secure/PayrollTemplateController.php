<?php

namespace App\Http\Controllers\Secure;

use Aloha\Twilio\Twilio;
use App\Helpers\BulkSms;
use App\Helpers\Flash;
use App\Http\Requests;
use App\Models\Borrower;
use App\Models\CustomField;
use App\Models\CustomFieldMeta;
use App\Models\PayrollTemplate;
use App\Models\PayrollTemplateMeta;
use App\Models\Setting;
use App\Models\User;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Clickatell\Api\ClickatellHttp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PayrollTemplateController extends SecureController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Sentinel::hasAccess('payroll.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $data = PayrollTemplate::all();

        return view('payroll_template.data', compact('data'));
    }

    public function edit($id)
    {
        if (! Sentinel::hasAccess('payroll.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $top_left = PayrollTemplateMeta::where('payroll_template_id', $id)->where('position', 'top_left')->get();
        $top_right = PayrollTemplateMeta::where('payroll_template_id', $id)->where('position', 'top_right')->get();
        $bottom_left = PayrollTemplateMeta::where('payroll_template_id', $id)->where('position', 'bottom_left')->get();
        $bottom_right = PayrollTemplateMeta::where('payroll_template_id', $id)->where('position',
            'bottom_right')->get();

        return view('payroll_template.edit',
            compact('id', 'bottom_right', 'bottom_left', 'top_right', 'top_left'));
    }

    public function add_row(Request $request, $id)
    {
        if (! Sentinel::hasAccess('payroll.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $meta = new PayrollTemplateMeta();
        $meta->name = $request->name;
        $meta->payroll_template_id = $id;
        $meta->position = $request->position;
        $meta->save();
        Flash::success(trans('general.successfully_saved'));

        return redirect('payroll/template/'.$id.'/edit');
    }

    public function update(Request $request, $id)
    {
        if (! Sentinel::hasAccess('payroll.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $metas = PayrollTemplateMeta::where('payroll_template_id', $id)->get();
        foreach ($metas as $key) {
            $meta = PayrollTemplateMeta::find($key->id);
            $kid = $key->id;
            $meta->name = $request->$kid;
            $meta->save();
        }
        Flash::success(trans('general.successfully_saved'));

        return redirect('payroll/template');
    }

    public function delete_meta(Request $request)
    {
        if (! Sentinel::hasAccess('payroll.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        PayrollTemplateMeta::destroy($request->meta_id);
    }
}
