<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Models\AuditTrail;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class AuditTrailController extends SecureController
{
    /*public function __construct()
    {
        $this->middleware('sentinel');
    }*/

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Sentinel::hasAccess('audit_trail')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        /*$data = AuditTrail::where('company_id', session('current_company'))->get();*/
        $data = AuditTrail::get();

        return view('audit_trail.data', compact('data'));
    }

    public function delete($id)
    {
        if (! Sentinel::hasAccess('audit_trail')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        AuditTrail::destroy($id);
        Flash::success(trans('general.successfully_deleted'));

        return redirect('audit_trail/data');
    }
}
