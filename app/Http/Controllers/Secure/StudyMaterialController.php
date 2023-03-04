<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\StudyMaterialRequest;
use App\Models\DocumentType;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Repositories\StudyMaterialRepository;
use App\Repositories\TeacherSubjectRepository;
use Yajra\DataTables\Facades\DataTables;

class StudyMaterialController extends SecureController
{
    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;

    /**
     * @var StudyMaterialRepository
     */
    private $studyMaterialRepository;

    /**
     * DairyController constructor.
     * @param TeacherSubjectRepository $teacherSubjectRepository
     * @param StudyMaterialRepository $studyMaterialRepository
     */
    public function __construct(
        TeacherSubjectRepository $teacherSubjectRepository,
        StudyMaterialRepository $studyMaterialRepository
    ) {
        parent::__construct();

        $this->teacherSubjectRepository = $teacherSubjectRepository;
        $this->studyMaterialRepository = $studyMaterialRepository;

        view()->share('type', 'study_material');

        $columns = ['title', 'subject', 'student_group', 'date_off', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('study_material.study_material');

        $studyMaterials = $this->studyMaterialRepository->getAllForStudent(session('current_employee'))
                ->get();

        return view('study_material.index', compact('title', 'studyMaterials'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function video()
    {
        $title = trans('study_material.study_material');

        $studyMaterials = $this->studyMaterialRepository->getAllForStudent(session('current_employee'))
                ->get();

        return view('study_material.video', compact('title', 'studyMaterials'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param Subject $subject
     * @return Response
     */
    public function subject(Subject $subject)
    {
        $title = trans('study_material.study_material');

        return view('study_material.index', compact('title', 'subject'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('study_material.new');
        $documentTypes = $this->generateParams();

        return view('layouts.create', compact('title', 'documentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StudyMaterialRequest $request
     * @return Response
     */
    public function store(StudyMaterialRequest $request)
    {
        $studyMaterial = new StudyMaterial($request->except('file_file'));
        if ($request->hasFile('file_file') != '') {
            $file = $request->file('file_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/study_material/';
            $file->move($destinationPath, $picture);
            $studyMaterial->file = $picture;
        }
        $studyMaterial->employee_id = session('current_employee');
        $studyMaterial->company_id = session('current_company');
        $studyMaterial->company_year_id = session('current_company_year');
        $studyMaterial->save();

        return redirect('/study_material');
    }

    /**
     * Display the specified resource.
     *
     * @param StudyMaterial $studyMaterial
     * @return Response
     */
    public function show(StudyMaterial $studyMaterial)
    {
        $title = trans('study_material.details');
        $action = 'show';

        return view('study_material._details', compact('studyMaterial', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  StudyMaterial $studyMaterial
     * @return Response
     */
    public function edit(StudyMaterial $studyMaterial)
    {
        $title = trans('study_material.edit');
        $documentTypes = $this->generateParams();

        return view('layouts.edit', compact('title', 'studyMaterial', 'documentTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StudyMaterial $studyMaterial
     * @param StudyMaterialRequest $request
     * @return Response
     */
    public function update(StudyMaterialRequest $request, StudyMaterial $studyMaterial)
    {
        if ($request->hasFile('file_file') != '') {
            $file = $request->file('file_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/study_material/';
            $file->move($destinationPath, $picture);
            $studyMaterial->file = $picture;
        }
        $studyMaterial->update($request->except('file_file'));

        return redirect('/study_material');
    }

    /**
     * @param StudyMaterial $studyMaterial
     * @return Response
     */
    public function delete(StudyMaterial $studyMaterial)
    {
        $title = trans('study_material.delete');

        return view('/study_material/delete', compact('studyMaterial', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  StudyMaterial $studyMaterial
     * @return Response
     */
    public function destroy(StudyMaterial $studyMaterial)
    {
        $studyMaterial->delete();

        return redirect('/study_material');
    }

    public function download(StudyMaterial $studyMaterial)
    {
        return response()->download($studyMaterial->file_url);
    }

    public function data($subject_id, Datatables $datatables)
    {
        if ($this->user->inRole('teacher')) {
            $studyMaterials = $this->studyMaterialRepository
                ->getAllForUserAndGroup($this->user->id, session('current_student_group'))
                ->with('subject', 'student_group')
                ->get();
        } elseif ($this->user->inRole('student')) {
            $studyMaterials = $this->studyMaterialRepository->getAllForStudent($this->user->id)
                ->with('subject', 'student_group')
                ->get();
        } elseif ($this->user->inRole('parent')) {
            $user = session('current_student_user_id');

            $studyMaterials = $this->studyMaterialRepository->getAllForStudent($user)
                ->with('subject', 'student_group')
                ->get();
        } else {
            $studyMaterials = [];
        }

        if ($subject_id > 0) {
            $studyMaterials = $studyMaterials->filter(function ($item) use ($subject_id) {
                return $item->subject_id == $subject_id;
            });
        }
        $studyMaterials = $studyMaterials->map(function ($studyMaterial) {
            return [
                'id' => $studyMaterial->id,
                'title' => $studyMaterial->title,
                'subject' => isset($studyMaterial->subject) ? $studyMaterial->subject->title : '',
                'student_group' => isset($studyMaterial->student_group) ? $studyMaterial->student_group->title : '',
                'date_off' => $studyMaterial->date_off,
            ];
        });
        if ($this->user->inRole('teacher')) {
            return Datatables::make($studyMaterials)
                ->addColumn('actions', '<a href="{{ url(\'/study_material/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/study_material/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/study_material/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
                ->removeColumn('id')
                 ->rawColumns(['actions'])->make(false);
        } else {
            return Datatables::make($studyMaterials->toBase()->unique())
                ->addColumn('actions', '<a href="{{ url(\'/study_material/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                ->removeColumn('id')
                 ->rawColumns(['actions'])->make();
        }
    }

    /**
     * @return mixed
     */
    private function generateParams()
    {
        $documentTypes = DocumentType::get()
                ->map(function ($documentType) {
                    return [
                        'id' => $documentType->id,
                        'title' => $documentType->title,
                    ];
                })->pluck('title', 'id')->prepend('Select Document Type', '')->toArray();

        return $documentTypes;
    }
}
