<?php

namespace App\Http\Controllers\Secure;

use Aloha\Twilio\Twilio;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Http\Requests\DistrictRequest;
use App\Models\CustomField;
use App\Models\CustomFieldMeta;
use App\Models\District;
use App\Models\Setting;
use App\Models\User;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class DistrictController extends SecureController
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
        if (! Sentinel::hasAccess('offices.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $data = District::with('region')->get();

        return view('district.data', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Sentinel::hasAccess('offices.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        //get custom fields
        return view('district.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DistrictRequest $request)
    {
        if (! Sentinel::hasAccess('offices.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $district = new District();
        $district->name = $request->name;
        $district->region_id = $request->region_id;
        $district->capital = $request->capital;
        $district->save();
        GeneralHelper::audit_trail('Create', 'Districts', $district->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect('district/data');
    }

    public function show($district)
    {
        if (! Sentinel::hasAccess('offices.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        return view('district.show', compact('district'));
    }

    public function edit($district)
    {
        if (! Sentinel::hasAccess('offices.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        return view('district.edit', compact('district'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DistrictRequest $request, $id)
    {
        if (! Sentinel::hasAccess('offices.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $district = District::find($id);
        $district->name = $request->name;
        $district->region_id = $request->region_id;
        $district->capital = $request->capital;
        $district->save();
        GeneralHelper::audit_trail('Update', 'Districts', $district->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect('district/data');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        if (! Sentinel::hasAccess('offices.delete')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $district = District::find($id);
        if ($district->id == 1) {
            Flash::warning('You cannot delete default office. Its needed to keep things working well.');

            return redirect()->back();
        }
        District::destroy($id);
        GeneralHelper::audit_trail('Delete', 'Districts', $district->id);
        Flash::success(trans('general.successfully_deleted'));

        return redirect('district/data');
    }
}
