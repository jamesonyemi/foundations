<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\CustomUserFieldRequest;
use App\Models\CustomUserField;
use App\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class CustomUserFieldController extends SecureController
{
    public function __construct()
    {
        parent::__construct();
        view()->share('type', 'custom_user_field');

        $columns = ['role', 'title', 'type', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('custom_user_field.custom_user_fields');

        return view('custom_user_field.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('custom_user_field.new');
        $roles = Role::pluck('name', 'id')->toArray();
        $types = ['text' => 'Text Box',
            'number' => 'Number',
            'email' => 'Email',
            'url' => 'URL',
            'date' => 'Date',
            'select' => 'Select Box',
            'radio' => 'Radio Button',
            'checkbox' => 'Check Box',
            'textarea' => 'Textarea', ];

        return view('layouts.create', compact('title', 'roles', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CustomUserFieldRequest|Request $request
     * @return Response
     */
    public function store(CustomUserFieldRequest $request)
    {
        $customUserField = new CustomUserField($request->all());
        $customUserField->name = str_slug($request->get('title'));
        $customUserField->company_id = session('current_company');
        $customUserField->save();

        return redirect('/custom_user_field');
    }

    /**
     * Display the specified resource.
     *
     * @param CustomUserField $customUserField
     * @return Response
     * @internal param int $id
     */
    public function show(CustomUserField $customUserField)
    {
        $title = trans('custom_user_field.details');
        $action = 'show';

        return view('layouts.show', compact('title', 'customUserField', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CustomUserField $customUserField
     * @return Response
     * @internal param int $id
     */
    public function edit(CustomUserField $customUserField)
    {
        $title = trans('custom_user_field.edit');
        $roles = Role::pluck('name', 'id')->toArray();
        $types = ['text' => 'Text Box',
            'number' => 'Number',
            'email' => 'Email',
            'url' => 'URL',
            'date' => 'Date',
            'select' => 'Select Box',
            'radio' => 'Radio Button',
            'checkbox' => 'Check Box',
            'textarea' => 'Textarea', ];

        return view('layouts.edit', compact('title', 'customUserField', 'roles', 'types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CustomUserFieldRequest|Request $request
     * @param CustomUserField $customUserField
     * @return Response
     */
    public function update(CustomUserFieldRequest $request, CustomUserField $customUserField)
    {
        $customUserField->name = str_slug($request->get('title'));
        $customUserField->update($request->all());

        return redirect('/custom_user_field');
    }

    public function delete(CustomUserField $customUserField)
    {
        $title = trans('custom_user_field.delete');

        return view('custom_user_field.delete', compact('title', 'customUserField'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CustomUserField $customUserField
     * @return Response
     */
    public function destroy(CustomUserField $customUserField)
    {
        $customUserField->delete();

        return redirect('/custom_user_field');
    }

    public function data()
    {
        $customUserFields = CustomUserField::where('company_id', session('current_company'))->get()
            ->map(function ($customUserField) {
                return [
                    'id' => $customUserField->id,
                    'role' => isset($customUserField->role) ? $customUserField->role->name : '',
                    'title' => $customUserField->title,
                    'type' => $customUserField->type,
                ];
            });

        return Datatables::make($customUserFields)
            ->addColumn('actions', '<a href="{{ url(\'/custom_user_field/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/custom_user_field/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/custom_user_field/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }
}
