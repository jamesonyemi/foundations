<?php

namespace App\Http\Controllers\Secure;

use Aloha\Twilio\Twilio;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Http\Requests;
use App\Models\CustomField;
use App\Models\CustomFieldMeta;
use App\Models\Office;
use App\Models\OfficeUser;
use App\Models\Region;
use App\Models\Setting;
use App\Models\User;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegionController extends SecureController
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
        $data = Region::with('districts', 'schools')->get();

        return view('region.data', compact('data'));
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
        return view('region.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Sentinel::hasAccess('offices.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $office = new Region();
        $office->name = $request->name;
        $office->capital = $request->capital;
        $office->save();
        GeneralHelper::audit_trail('Create', 'Regions', $office->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect('region/data');
    }

    public function show($region)
    {
        if (! Sentinel::hasAccess('offices.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        return view('region.show', compact('region'));
    }

    public function edit($region)
    {
        if (! Sentinel::hasAccess('offices.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        return view('region.edit', compact('region'));
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
        if (! Sentinel::hasAccess('offices.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $office = Region::find($id);
        $office->name = $request->name;
        $office->capital = $request->capital;
        $office->save();
        GeneralHelper::audit_trail('Update', 'Regions', $office->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect('region/data');
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
        $region = Region::find($id);
        if ($region->id == 1) {
            Flash::warning('You cannot delete default office. Its needed to keep things working well.');

            return redirect()->back();
        }
        Region::destroy($id);
        GeneralHelper::audit_trail('Delete', 'Regions', $region->id);
        Flash::success(trans('general.successfully_deleted'));

        return redirect('region/data');
    }
}
