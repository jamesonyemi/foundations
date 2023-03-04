<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Models\CustomField;
use App\Models\CustomFieldMeta;
use App\Models\PaymentType;
use App\Models\Setting;
use App\Models\User;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PaymentTypeController extends Controller
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
        if (! Sentinel::hasAccess('products.payment_types.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $data = PaymentType::all();

        return view('payment_type.data', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Sentinel::hasAccess('products.payment_types.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        //get custom fields
        return view('payment_type.create');
    }

    public function store(Request $request)
    {
        if (! Sentinel::hasAccess('products.payment_types.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $payment_type = new PaymentType();
        $payment_type->name = $request->name;
        $payment_type->notes = $request->notes;
        $payment_type->save();
        Flash::success(trans('general.successfully_saved'));

        return redirect('payment_type/data');
    }

    public function show($payment_type)
    {
        if (! Sentinel::hasAccess('products.payment_types.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        return view('payment_type.show', compact('payment_type'));
    }

    public function edit($payment_type)
    {
        if (! Sentinel::hasAccess('products.payment_types.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        return view('payment_type.edit', compact('payment_type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Sentinel::hasAccess('products.payment_types.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $payment_type = PaymentType::find($id);
        $payment_type->name = $request->name;
        $payment_type->notes = $request->notes;
        $payment_type->save();
        Flash::success(trans('general.successfully_saved'));

        return redirect('payment_type/data');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        if (! Sentinel::hasAccess('products.payment_types.delete')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        PaymentType::destroy($id);
        Flash::success(trans('general.successfully_deleted'));

        return redirect('payment_type/data');
    }
}
