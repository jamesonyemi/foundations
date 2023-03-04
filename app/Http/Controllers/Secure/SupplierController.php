<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\AddSupplierDocumentRequest;
use App\Http\Requests\Secure\AddSupplierItemsRequest;
use App\Http\Requests\Secure\AddSupplierRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\ProcurementCategoryRequest;
use App\Http\Requests\Secure\ProcurementItemRequest;
use App\Http\Requests\Secure\SupplierRequest;
use App\Models\Level;
use App\Models\ProcurementCategory;
use App\Models\ProcurementCategorySupplier;
use App\Models\ProcurementItem;
use App\Models\ProcurementItemSupplier;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Models\UserDocument;
use App\Repositories\LevelRepository;
use App\Repositories\SectionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class SupplierController extends SecureController
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
        /*$this->middleware('authorized:supplier.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:supplier.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:supplier.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:supplier.delete', ['only' => ['delete', 'destroy']]);*/
        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'supplier');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('supplier.supplier');
        $suppliers = Supplier::get();

        return view('supplier.index', compact('title', 'suppliers'));
    }

    public function contract()
    {
        $title = 'Contract Management';
        $suppliers = Supplier::get();

        return view('supplier.contract_index', compact('title', 'suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('procurement.new_category');

        $procurementCategoryCategories = ProcurementCategory::get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->title ?? '',
                ];
            })->pluck('name', 'id')
            ->toArray();

        return view('layouts.create', compact('title', 'procurementCategoryCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(SupplierRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $supplier = Supplier::firstOrCreate(
                                [
                                    'employee_id' => session('current_employee'),
                                    'title' => $request['title'],
                                    'tin_number' => $request['tin_number'],
                                    'description' => $request['description'],
                                    'contact_person' => $request['contact_person'],
                                    'phone_number' => $request['phone_number'],
                                    'email_address' => $request['email_address'],
                                    'website' => $request['website'],
                                    'location' => $request['location'],
                                    'contract_start_date' => $request['contract_start_date'],
                                    'contract_end_date' => $request['contract_end_date'],
                                ]
                            );

                $supplier->procurementCategories()->attach($request->input('procurement_category_id'));
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Supplier Created Successfully</div>');
    }

    public function addSupplierItems(AddSupplierItemsRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $item = ProcurementItemSupplier::updateOrCreate(
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
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        $supplier = Supplier::find($request->supplier_id);

        return view('supplier.supplierItems', compact('supplier'));
    }

    public function addSupplierDocuments(AddSupplierDocumentRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if ($request->hasFile('file') != '') {
                    $file = $request->file('file');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/documents/';
                    $file->move($destinationPath, $document);

                    $supplierDocument = new SupplierDocument;
                    $supplierDocument->supplier_id = $request->supplier_id;
                    $supplierDocument->document_title = $request->document_title;
                    $supplierDocument->file = $document;
                    $supplierDocument->expiry_date = $request->expiry_date;
                    $supplierDocument->remarks = $request->remarks;
                    $supplierDocument->save();
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        $supplier = Supplier::find($request->supplier_id);

        return view('supplier.supplierDocuments', compact('supplier'));
    }

    /**
     * Display the specified resource.
     *
     * @param ProcurementCategory $procurementCategory
     * @return Response
     */
    public function show(Supplier $supplier)
    {
        $title = trans('supplier.details');
        $action = 'show';
        $ids = $supplier->procurementCategories
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                ];
            })->pluck('id')
            ->toArray();

        $items = ProcurementItem::whereHas('procurementCategory', function ($q) use ($ids) {
            $q->whereIn('procurement_items.procurement_category_id', $ids);
        })->get()
            ->pluck('title', 'id')
            ->prepend(trans('procurement.select_item'), '')
            ->toArray();

        return view('layouts.show', compact('supplier', 'title', 'action', 'items'));
    }

    public function showContract(Supplier $supplier)
    {
        $title = 'Supplier Documents';
        $action = 'show';

        return view('supplier._contract_details', compact('supplier', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Supplier $supplier
     * @return Response
     */
    public function edit(Supplier $supplier)
    {
        $title = trans('procurement.edit_category');

        $procurementCategoryCategories = ProcurementCategory::get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->title ?? '',
                ];
            })->pluck('name', 'id')
            ->toArray();

        $procurement_category_supplier = ProcurementCategorySupplier::where('supplier_id', $supplier->id)->get()
            ->pluck('procurement_category_id')
            ->toArray();

        return view('layouts.edit', compact('title', 'supplier', 'procurementCategoryCategories', 'procurement_category_supplier'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        try {
            DB::transaction(function () use ($request, $supplier) {
                $supplier->update($request->except('procurement_category_id'));
                $tags = $request->input('procurement_category_id');

                $supplier->procurementCategories()->sync($tags);
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Supplier Updated Successfully</div>');
    }

    public function delete(Supplier $supplier)
    {
        if ($supplier->items->count() > 0) {
            return response()->json(['exception'=>'Supplier has items associations and cannot be deleted']);
        }
        try {
            DB::transaction(function () use ($supplier) {
                $supplier->procurementCategories()->delete();
                $supplier->documents()->delete();
                $supplier->delete();
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('Supplier Deleted Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return 'Supplier Deleted';
    }

    public function deleteSupplierItem(ProcurementItemSupplier $procurementItemSupplier)
    {
        $supplier = Supplier::find($procurementItemSupplier->supplier_id);
        try {
            DB::transaction(function () use ($procurementItemSupplier) {
                $procurementItemSupplier->delete();
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return view('supplier.supplierItems', compact('supplier'));
    }

    public function deleteSupplierDocument(SupplierDocument $supplierDocument)
    {
        $supplier = Supplier::find($supplierDocument->supplier_id);
        try {
            DB::transaction(function () use ($supplierDocument) {
                $supplierDocument->delete();
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return view('supplier.supplierDocuments', compact('supplier'));
    }
}
