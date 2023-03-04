<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Models\AssetType;
use App\Models\CustomField;
use App\Models\CustomFieldMeta;
use App\Models\Setting;
use App\Models\User;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AssetTypeController extends SecureController
{
    public function __construct()
    {
        $this->middleware('sentinel');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Sentinel::hasAccess('assets.types.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $data = AssetType::where('company_id', session('current_company'))->get();

        return view('asset_type.data', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Sentinel::hasAccess('assets.types.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        //get custom fields
        return view('asset_type.create');
    }

    public function store(Request $request)
    {
        if (! Sentinel::hasAccess('assets.types.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $asset_type = new AssetType();
        $asset_type->name = $request->name;
        $asset_type->company_id = session('company_id');
        $asset_type->gl_account_asset_id = $request->gl_account_asset_id;
        $asset_type->gl_account_expense_id = $request->gl_account_expense_id;
        $asset_type->gl_account_fixed_asset_id = $request->gl_account_fixed_asset_id;
        $asset_type->gl_account_contra_asset_id = $request->gl_account_contra_asset_id;
        $asset_type->gl_account_liability_id = $request->gl_account_liability_id;
        $asset_type->gl_account_income_id = $request->gl_account_income_id;
        $asset_type->notes = $request->notes;
        $asset_type->save();
        GeneralHelper::audit_trail('Create', 'Asset Types', $asset_type->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect('asset/type/data');
    }

    public function show($asset_type)
    {
        if (! Sentinel::hasAccess('assets.types.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        return view('asset_type.show', compact('asset_type'));
    }

    public function edit($asset_type)
    {
        if (! Sentinel::hasAccess('assets.types.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        return view('asset_type.edit', compact('asset_type'));
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
        if (! Sentinel::hasAccess('assets.types.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $asset_type = AssetType::find($id);
        $asset_type->name = $request->name;
        $asset_type->gl_account_asset_id = $request->gl_account_asset_id;
        $asset_type->gl_account_expense_id = $request->gl_account_expense_id;
        $asset_type->gl_account_fixed_asset_id = $request->gl_account_fixed_asset_id;
        $asset_type->gl_account_contra_asset_id = $request->gl_account_contra_asset_id;
        $asset_type->gl_account_liability_id = $request->gl_account_liability_id;
        $asset_type->gl_account_income_id = $request->gl_account_income_id;
        $asset_type->notes = $request->notes;
        $asset_type->save();
        GeneralHelper::audit_trail('Update', 'Asset Types', $asset_type->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect('asset/type/data');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        if (! Sentinel::hasAccess('assets.types.delete')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        AssetType::destroy($id);
        GeneralHelper::audit_trail('Delete', 'Asset Types', $id);
        Flash::success(trans('general.successfully_deleted'));

        return redirect()->back();
    }
}
