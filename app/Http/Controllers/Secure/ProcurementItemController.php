<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Http\Requests\Secure\AddItemPlanRequest;
use App\Http\Requests\Secure\AddSupplierRequest;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Imports\EmployeesImport;
use App\Imports\ItemsImport;
use App\Models\Company;
use App\Models\ItemNumberSeries;
use App\Models\ProcurementCategory;
use App\Models\ProcurementItem;
use App\Models\ProcurementItemSupplier;
use App\Models\ProcurementMasterCategory;
use App\Models\ProcurementPlan;
use App\Models\Supplier;
use App\Repositories\SectionRepository;
use App\Repositories\LevelRepository;
use App\Helpers\Settings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use Illuminate\Http\Request;

class ProcurementItemController extends SecureController
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * DirectionController constructor.
     *
     * @param LevelRepository $levelRepository
     * @param SectionRepository $sectionRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        LevelRepository $levelRepository,
        SectionRepository $sectionRepository
    ) {

        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'procurementItem');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('procurement.items');
        $procurementItems = ProcurementItem::with(['procurementCategory', 'suppliers'])->get();
        return view('procurementItem.index', compact('title', 'procurementItems'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('procurement.new_procurement_item');

        $procurementItemCategories = ProcurementCategory::where('category_code', '!=', '')->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" =>$item->title. ' | ' .$item->category_code,
                ];
            })
            ->pluck("title", 'id')
            ->prepend(trans('procurement.categories'), '')
            ->toArray();

        $procurementMasterCategories = ProcurementMasterCategory::get()
            ->pluck('title', 'id')
            ->prepend(trans('procurement.master_categories'), '')
            ->toArray();


        return view('layouts.create', compact('title', 'procurementItemCategories', 'procurementMasterCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(ProcurementItemRequest $request)
    {

        try
        {
            DB::transaction(function() use ($request) {
                $category = ProcurementCategory::find($request['procurement_category_id']);
                $itemNumberSeries = ItemNumberSeries::first();
                $item = ProcurementItem::firstOrCreate
                (
                    [
                        'employee_id' => session('current_employee'),
                        'title' => $request['title'],
                        'item_code' => $category->category_code .'/'. str_pad($itemNumberSeries->next_number, 10, '0', STR_PAD_LEFT),
                        'procurement_category_id' => $request['procurement_category_id'],
                        'description' => $request['description'],
                    ]
                );

                $itemNumberSeries->next_number = $itemNumberSeries->next_number + $itemNumberSeries->number_interval;
                $itemNumberSeries->save();
            });
        }

        catch (\Exception $e) {

            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">ITEM CREATED Successfully</div>') ;


    }



    public function addItemSuppliers(AddSupplierRequest $request)
    {

        try
        {
            DB::transaction(function() use ($request) {
                $item = ProcurementItemSupplier::updateOrCreate
                (
                    [
                        'supplier_id' => $request['supplier_id'],
                        'procurement_item_id' => $request['procurement_item_id'],
                    ],
                    [
                        'employee_id' => session('current_employee'),
                        'price' => $request['price'],
                        'quantity_in_stock' => $request['quantity_in_stock'],
                        'remarks' => $request['remarks'],
                    ]
                );

            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }

        $procurementItem = ProcurementItem::find($request->procurement_item_id);

        return view('procurement.itemSuppliers', compact('procurementItem'));
    }

    /**
     * Display the specified resource.
     *
     * @param ProcurementCategory $procurementCategory
     * @return Response
     */
    public function show(ProcurementItem $procurementItem)
    {
        $title = trans('procurement.item_details');
        $action = 'show';
        $suppliers = $procurementItem->procurementCategory->suppliers
            ->pluck('title', 'id')
            ->prepend(trans('procurement.select_supplier'), '')
            ->toArray();

        return view('procurementItem._details', compact('procurementItem', 'title', 'action', 'suppliers'));
    }






    /**
     * Show the form for editing the specified resource.
     *
     * @param ProcurementItem $procurementItem
     * @return Response
     */
    public function edit(ProcurementItem $procurementItem)
    {
        $title = trans('procurement.edit_item');

        $procurementItemCategories = ProcurementCategory::where('category_code', '!=', '')->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" =>$item->title. ' | ' .$item->category_code,
                ];
            })
            ->pluck("title", 'id')
            ->prepend(trans('procurement.categories'), '')
            ->toArray();

        $procurementMasterCategories = ProcurementMasterCategory::get()
            ->pluck('title', 'id')
            ->prepend(trans('procurement.master_categories'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'procurementItem', 'procurementItemCategories', 'procurementMasterCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(ProcurementItemRequest $request, ProcurementItem $procurementItem)
    {
        $procurementItem->update($request->except('procurement_master_category_id'));


        return 'Item updated';
    }

    public function delete(ProcurementItem $procurementItem)
    {
        if ($procurementItem->procurements->count() > 0)
            return response()->json(['exception'=>'Item has request associations and cannot be deleted']);

        /*if ($procurementItem->suppliers->count() > 0)
            return response()->json(['exception'=>'Item has Supplier associations and cannot be deleted']);*/
        try
        {
            DB::transaction(function() use ($procurementItem) {
                $procurementItem->delete();
            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }
        return response('Item Deleted Successfully') ;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy(ProcurementItem $procurementItem)
    {
        $procurementItem->delete();
        return 'Item Deleted';


    }

    public function deleteItemSupplier(ProcurementItemSupplier $procurementItemSupplier)
    {
        $procurement_item_id = $procurementItemSupplier->procurement_item_id;
        try
        {
            DB::transaction(function() use ($procurementItemSupplier) {
                $procurementItemSupplier->delete();

            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }

        $procurementItem = ProcurementItem::find($procurement_item_id);

        return view('procurement.itemSuppliers', compact('procurementItem'));


    }



    public function findSectionLevel(Request $request)
    {
        $directions = $this->levelRepository
            ->getAllForSection($request->section_id)
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), 0)
            ->toArray();
        return $directions;
    }



    public function getImport()
    {
        $title = trans('student.import_student');

        return view('procurementItem.import', compact('title'));
    }

    public function postImport(ImportRequest $request)
    {
        $title = trans('employee.import_student');

        Excel::import(new ItemsImport(), $request->file('file'));

        Flash::success('Items Imported Successfully');
        return redirect('/');
    }


    public function planIndex()
    {
        $title = 'Procurement Plan';
        $companies = Company::where('active', 'Yes')->with(['sector'])->get();
        return view('procurementItem.plan', compact('title', 'companies'));
    }

    public function planShow(Company $company)
    {
        $title = 'Procurement Plan';
        $action = 'show';
        $procurementItems = ProcurementItem::with(['procurementCategory', 'suppliers'])
            ->orderBy('title', 'ASC')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "title" => isset($item) ? $item->title. '-' .$item->item_code : "",
                ];
            })
            ->pluck('title', 'id')
            ->prepend('Select Item', '')
            ->toArray();

        return view('procurementItem._planDetails', compact('company', 'title', 'action', 'procurementItems'));
    }


    public function addItemPlan(AddItemPlanRequest $request)
    {

        try
        {
            DB::transaction(function() use ($request) {
                $item = ProcurementPlan::updateOrCreate
                (
                    [
                        'company_id' => $request['company_id'],
                        'procurement_item_id' => $request['procurement_item_id'],
                        'company_year_id' => session('current_company_year'),
                    ],

                    [
                        'employee_id' => session('current_employee'),
                        'company_year_id' => session('current_company_year'),
                        'quantity' => $request['quantity'],
                        'remarks' => $request['remarks'],
                    ]
                );

            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }

        $company = Company::find($request->company_id);

        return view('procurementItem.companyItemPlans', compact('company'));
    }


    public function deletePlan(ProcurementPlan $procurementPlan)
    {
        $company_id = $procurementPlan->company_id;
        /*if ($procurementItem->procurements->count() > 0)
            return response()->json(['exception'=>'Item has request associations and cannot be deleted']);*/

        /*if ($procurementItem->suppliers->count() > 0)
            return response()->json(['exception'=>'Item has Supplier associations and cannot be deleted']);*/
        try
        {
            DB::transaction(function() use ($procurementPlan) {
                $procurementPlan->delete();
            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }

        $company = Company::find($company_id);

        return view('procurementItem.companyItemPlans', compact('company'));
    }


}
