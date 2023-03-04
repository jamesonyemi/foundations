<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\AssetIdentification;
use App\Models\AssetNextOfKin;
use App\Models\CustomField;
use App\Models\CustomFieldMeta;
use App\Models\Document;
use App\Models\GlJournalEntry;
use App\Models\Loan;
use App\Models\Note;
use App\Models\Office;
use App\Models\Setting;
use Carbon\Carbon;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class AssetController extends SecureController
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
        if (! Sentinel::hasAccess('assets.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $data = Asset::where('company_id', session('current_company'))->get();

        return view('asset.data', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Sentinel::hasAccess('assets.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        //get custom fields
        $custom_fields = CustomField::where('category', 'assets')->get();

        return view('asset.create', compact('custom_fields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Sentinel::hasAccess('assets.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $asset = new Asset();

        $asset->created_by_id = Sentinel::getUser()->id;
        $asset->company_id = session('company_id');
        $asset->asset_type_id = $request->asset_type_id;
        $asset->office_id = $request->office_id;
        $asset->name = $request->name;
        $asset->purchase_date = $request->purchase_date;
        $asset->purchase_price = $request->purchase_price;
        $asset->value = $request->purchase_price;
        $asset->life_span = $request->life_span;
        $asset->salvage_value = $request->salvage_value;
        $asset->notes = $request->notes;
        $asset->save();
        //check custom fields
        if (Setting::where('company_id', session('current_company'))->where('setting_key', 'enable_custom_fields')->first()->setting_value == 1) {
            $custom_fields = CustomField::where('category', 'assets')->get();
            foreach ($custom_fields as $key) {
                $custom_field = new CustomFieldMeta();
                $id = 'custom_field_'.$key->id;
                if ($key->field_type == 'checkbox') {
                    if (! empty($request->$id)) {
                        $custom_field->name = serialize($request->$id);
                    } else {
                        $custom_field->name = serialize([]);
                    }
                } else {
                    $custom_field->name = $request->$id;
                }
                $custom_field->parent_id = $asset->id;
                $custom_field->custom_field_id = $key->id;
                $custom_field->category = 'assets';
                $custom_field->save();
            }
        }
        //debit and credit the necessary accounts
        if (! empty($asset->type->gl_account_fixed_asset)) {
            $journal = new GlJournalEntry();
            $journal->created_by_id = Sentinel::getUser()->id;
            $journal->company_id = session('company_id');
            $journal->office_id = $asset->office_id;
            $journal->gl_account_id = $asset->type->gl_account_fixed_asset_id;
            $journal->date = $asset->purchase_date;
            $date = explode('-', $asset->purchase_date);
            $journal->year = $date[0];
            $journal->month = $date[1];
            $journal->transaction_type = 'asset';
            $journal->name = 'Purchase Asset';
            $journal->debit = $asset->amount;
            $journal->reference = $asset->id;
            $journal->save();
        }
        if (! empty($asset->type->gl_account_asset)) {
            $journal = new GlJournalEntry();
            $journal->created_by_id = Sentinel::getUser()->id;
            $journal->company_id = session('company_id');
            $journal->office_id = $asset->office_id;
            $journal->gl_account_id = $asset->type->gl_account_asset_id;
            $journal->date = $asset->purchase_date;
            $date = explode('-', $asset->purchase_date);
            $journal->year = $date[0];
            $journal->month = $date[1];
            $journal->transaction_type = 'asset';
            $journal->name = 'Purchase Asset';
            $journal->credit = $asset->amount;
            $journal->reference = $asset->id;
            $journal->save();
        }
        $date = explode('-', $asset->purchase_date);
        $diff = date('Y') - $date[0];
        //check for past year depreciation
        if ($diff > 0) {
            $year = $date[0];
            for ($i = 0; $i < $diff; $i++) {
                if ($asset->value > $asset->salvage_value) {
                    $asset_depreciation = new AssetDepreciation();
                    $asset_depreciation->asset_id = $asset->id;
                    $asset_depreciation->year = $year;
                    $asset_depreciation->beginning_value = $asset->value;
                    $asset_depreciation->depreciation_value = ($asset->purchase_price - $asset->salvage_value) / $asset->life_span;
                    $asset_depreciation->ending_value = $asset_depreciation->beginning_value - $asset_depreciation->depreciation_value;
                    $asset_depreciation->save();
                    $asset->value = $asset_depreciation->ending_value;
                    $asset->save();
                    //debit and credit the necessary accounts
                    if (! empty($asset->type->gl_account_expense)) {
                        $journal = new GlJournalEntry();
                        $journal->created_by_id = Sentinel::getUser()->id;
                        $journal->company_id = session('company_id');
                        $journal->office_id = $asset->office_id;
                        $journal->gl_account_id = $asset->type->gl_account_expense_id;
                        $journal->date = $year.'-12-31';
                        $date = explode('-', $year.'-12-31');
                        $journal->year = $date[0];
                        $journal->month = $date[1];
                        $journal->transaction_type = 'asset';
                        $journal->name = 'Asset Depreciation';
                        $journal->debit = $asset_depreciation->depreciation_value;
                        $journal->reference = $asset->id;
                        $journal->save();
                    }
                    if (! empty($asset->type->gl_account_contra_asset)) {
                        $journal = new GlJournalEntry();
                        $journal->created_by_id = Sentinel::getUser()->id;
                        $journal->company_id = session('company_id');
                        $journal->office_id = $asset->office_id;
                        $journal->gl_account_id = $asset->type->gl_account_contra_asset_id;
                        $journal->date = $year.'-12-31';
                        $date = explode('-', $year.'-12-31');
                        $journal->year = $date[0];
                        $journal->month = $date[1];
                        $journal->transaction_type = 'asset';
                        $journal->name = 'Asset Depreciation';
                        $journal->credit = $asset_depreciation->depreciation_value;
                        $journal->reference = $asset->id;
                        $journal->save();
                    }
                }
                $year++;
                //make the necessary journals
            }
        }
        GeneralHelper::audit_trail('Create', 'Assets', $asset->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect('asset/data');
    }

    public function show($asset)
    {
        if (! Sentinel::hasAccess('assets.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        //get custom fields
        $custom_fields = CustomFieldMeta::where('category', 'assets')->where('parent_id', $asset->id)->get();

        return view('asset.show', compact('asset', 'custom_fields'));
    }

    public function edit($asset)
    {
        if (! Sentinel::hasAccess('assets.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        return view('asset.edit', compact('asset'));
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
        if (! Sentinel::hasAccess('assets.update')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $asset = Asset::find($id);
        $asset->asset_type_id = $request->asset_type_id;
        $asset->office_id = $request->office_id;
        $asset->name = $request->name;
        $asset->purchase_date = $request->purchase_date;
        $asset->purchase_price = $request->purchase_price;
        $asset->value = $request->purchase_price;
        $asset->life_span = $request->life_span;
        $asset->salvage_value = $request->salvage_value;
        $asset->notes = $request->notes;
        $asset->save();
        if (Setting::where('setting_key', 'enable_custom_fields')->first()->setting_value == 1) {
            $custom_fields = CustomField::where('category', 'assets')->get();
            foreach ($custom_fields as $key) {
                if (! empty(CustomFieldMeta::where('custom_field_id', $key->id)->where('parent_id', $id)->where('category',
                    'assets')->first())
                ) {
                    $custom_field = CustomFieldMeta::where('custom_field_id', $key->id)->where('parent_id',
                        $id)->where('category', 'assets')->first();
                } else {
                    $custom_field = new CustomFieldMeta();
                }
                $kid = 'custom_field_'.$key->id;
                if ($key->field_type == 'checkbox') {
                    if (! empty($request->$kid)) {
                        $custom_field->name = serialize($request->$kid);
                    } else {
                        $custom_field->name = serialize([]);
                    }
                } else {
                    $custom_field->name = $request->$kid;
                }
                $custom_field->parent_id = $id;
                $custom_field->custom_field_id = $key->id;
                $custom_field->category = 'assets';
                $custom_field->save();
            }
        }
        AssetDepreciation::where('asset_id', $id)->delete();
        GlJournalEntry::where('transaction_type', 'asset')->where('reference', $id)->delete();
        //debit and credit the necessary accounts
        if (! empty($asset->type->gl_account_fixed_asset)) {
            $journal = new GlJournalEntry();
            $journal->created_by_id = Sentinel::getUser()->id;
            $journal->office_id = $asset->office_id;
            $journal->gl_account_id = $asset->type->gl_account_fixed_asset_id;
            $journal->date = $asset->purchase_date;
            $date = explode('-', $asset->purchase_date);
            $journal->year = $date[0];
            $journal->month = $date[1];
            $journal->transaction_type = 'asset';
            $journal->name = 'Purchase Asset';
            $journal->debit = $asset->amount;
            $journal->reference = $asset->id;
            $journal->save();
        }
        if (! empty($asset->type->gl_account_asset)) {
            $journal = new GlJournalEntry();
            $journal->created_by_id = Sentinel::getUser()->id;
            $journal->office_id = $asset->office_id;
            $journal->gl_account_id = $asset->type->gl_account_asset_id;
            $journal->date = $asset->purchase_date;
            $date = explode('-', $asset->purchase_date);
            $journal->year = $date[0];
            $journal->month = $date[1];
            $journal->transaction_type = 'asset';
            $journal->name = 'Purchase Asset';
            $journal->credit = $asset->amount;
            $journal->reference = $asset->id;
            $journal->save();
        }
        $date = explode('-', $asset->purchase_date);
        $diff = date('Y') - $date[0];
        //check for past year depreciation
        if ($diff > 0) {
            $year = $date[0];
            for ($i = 0; $i < $diff; $i++) {
                if ($asset->value > $asset->salvage_value) {
                    $asset_depreciation = new AssetDepreciation();
                    $asset_depreciation->asset_id = $asset->id;
                    $asset_depreciation->year = $year;
                    $asset_depreciation->beginning_value = $asset->value;
                    $asset_depreciation->depreciation_value = ($asset->purchase_price - $asset->salvage_value) / $asset->life_span;
                    $asset_depreciation->ending_value = $asset_depreciation->beginning_value - $asset_depreciation->depreciation_value;
                    $asset_depreciation->save();
                    $asset->value = $asset_depreciation->ending_value;
                    $asset->save();
                    //debit and credit the necessary accounts
                    if (! empty($asset->type->gl_account_expense)) {
                        $journal = new GlJournalEntry();
                        $journal->created_by_id = Sentinel::getUser()->id;
                        $journal->office_id = $asset->office_id;
                        $journal->gl_account_id = $asset->type->gl_account_expense_id;
                        $journal->date = $year.'-12-31';
                        $date = explode('-', $year.'-12-31');
                        $journal->year = $date[0];
                        $journal->month = $date[1];
                        $journal->transaction_type = 'asset';
                        $journal->name = 'Asset Depreciation';
                        $journal->debit = $asset_depreciation->depreciation_value;
                        $journal->reference = $asset->id;
                        $journal->save();
                    }
                    if (! empty($asset->type->gl_account_contra_asset)) {
                        $journal = new GlJournalEntry();
                        $journal->created_by_id = Sentinel::getUser()->id;
                        $journal->office_id = $asset->office_id;
                        $journal->gl_account_id = $asset->type->gl_account_contra_asset_id;
                        $journal->date = $year.'-12-31';
                        $date = explode('-', $year.'-12-31');
                        $journal->year = $date[0];
                        $journal->month = $date[1];
                        $journal->transaction_type = 'asset';
                        $journal->name = 'Asset Depreciation';
                        $journal->credit = $asset_depreciation->depreciation_value;
                        $journal->reference = $asset->id;
                        $journal->save();
                    }
                }
                $year++;
                //make the necessary journals
            }
        }
        GeneralHelper::audit_trail('Update', 'Assets', $asset->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect('asset/data');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        if (! Sentinel::hasAccess('assets.delete')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        Asset::destroy($id);
        AssetDepreciation::where('asset_id', $id)->delete();
        //GlJournalEntry::where('transaction_type', 'asset')->where('reference', $id)->delete();
        GeneralHelper::audit_trail('Delete', 'Assets', $id);
        Flash::success(trans('general.successfully_deleted'));

        return redirect()->back();
    }
}
