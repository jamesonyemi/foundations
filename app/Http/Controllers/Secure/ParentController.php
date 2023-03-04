<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CustomFormUserFields;
use App\Helpers\ExcelfileValidator;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\ParentRequest;
use App\Models\ParentStudent;
use App\Models\User;
use App\Repositories\ExcelRepository;
use App\Repositories\ParentStudentRepository;
use App\Repositories\StudentRepository;
use App\Repositories\UserRepository;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class ParentController extends SecureController
{
    /**
     * @var ParentStudentRepository
     */
    private $parentStudentRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var StudentRepository
     */
    private $studentRepository;

    /**
     * @var ExcelRepository
     */
    private $excelRepository;

    /**
     * ParentController constructor.
     *
     * @param ParentStudentRepository $parentStudentRepository
     * @param UserRepository $userRepository
     * @param StudentRepository $studentRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(
        ParentStudentRepository $parentStudentRepository,
        UserRepository $userRepository,
        StudentRepository $studentRepository,
        ExcelRepository $excelRepository
    ) {
        parent::__construct();

        $this->parentStudentRepository = $parentStudentRepository;
        $this->userRepository = $userRepository;
        $this->studentRepository = $studentRepository;
        $this->excelRepository = $excelRepository;

        $this->middleware('authorized:parent.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:parent.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:parent.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:parent.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'parent');
        $columns = ['parent', 'student', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('parent.parent');

        return view('parent.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('parent.new');
        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->user_id,
                    'name' => $student->user->full_name,
                ];
            })->pluck('name', 'id')->toArray();
        $custom_fields = CustomFormUserFields::getCustomUserFields('parent');

        return view('layouts.create', compact('title', 'students', 'custom_fields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ParentRequest $request
     * @return Response
     */
    public function store(ParentRequest $request)
    {
        $user = Sentinel::registerAndActivate($request->except('student_id'));

        $role = Sentinel::findRoleBySlug('parent');
        $role->users()->attach($user);

        $user = User::find($user->id);

        if ($request->hasFile('image_file') != '') {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
            $user->picture = $picture;
            $user->save();
        }

        $user->update($request->except('student_id', 'password', 'image_file'));

        if (! empty($request['student_id'])) {
            foreach ($request['student_id'] as $student) {
                $parent = new ParentStudent();
                $parent->user_id_student = $student;
                $parent->user_id_parent = $user->id;
                $parent->activate = 1;
                $parent->save();
            }
        }
        CustomFormUserFields::storeCustomUserField('parent', $user->id, $request);

        return redirect('/parent');
    }

    /**
     * Display the specified resource.
     *
     * @param User $parentStudent
     * @return Response
     */
    public function show(User $parentStudent)
    {
        $title = trans('parent.details');
        $action = 'show';
        $custom_fields = CustomFormUserFields::getCustomUserFieldValues('parent', $parentStudent->id);
        $students = ParentStudent::join('users', 'users.id', '=', 'parent_students.user_id_student')
            ->where('user_id_parent', $parentStudent->id)->select('users.*')->get();

        return view('layouts.show', compact('parentStudent', 'title', 'action', 'custom_fields', 'students'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $parentStudent
     * @return Response
     */
    public function edit(User $parentStudent)
    {
        $title = trans('parent.edit');
        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->user_id,
                    'name' => $student->user->full_name,
                ];
            })->pluck('name', 'id')->toArray();

        foreach ($parentStudent->student_parent as $item) {
            $students_ids[] = $item->user_id_student;
        }
        $custom_fields = CustomFormUserFields::fetchCustomValues('parent', $parentStudent->id);

        return view('layouts.edit', compact('title', 'students', 'parentStudent', 'students_ids', 'custom_fields'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ParentRequest $request
     * @param  User $parentStudent
     * @return Response
     */
    public function update(ParentRequest $request, User $parentStudent)
    {
        if ($request->hasFile('image_file') != '') {
            $file = $request->file('image_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
            $parentStudent->picture = $picture;
            $parentStudent->save();
        }

        $parentStudent->update($request->except('student_id', 'image_file'));

        ParentStudent::where('user_id_parent', $parentStudent->id)->delete();

        if (! empty($request->student_id)) {
            foreach ($request->student_id as $student) {
                $parent = new ParentStudent();
                $parent->user_id_student = $student;
                $parent->user_id_parent = $parentStudent->id;
                $parent->activate = 1;
                $parent->save();
            }
        }
        CustomFormUserFields::updateCustomUserField('parent', $parentStudent->id, $request);

        return redirect('/parent');
    }

    /**
     * @param User $parentStudent
     * @return Response
     */
    public function delete(User $parentStudent)
    {
        $title = trans('parent.delete');
        $custom_fields = CustomFormUserFields::getCustomUserFieldValues('parent', $parentStudent->id);
        $students = ParentStudent::join('users', 'users.id', '=', 'parent_students.user_id_student')
            ->where('user_id_parent', $parentStudent->id)->select('users.*')->get();

        return view('parent.delete', compact('parentStudent', 'title', 'custom_fields', 'students'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $parentStudent
     * @return Response
     */
    public function destroy(User $parentStudent)
    {
        $parentStudent->delete();

        return redirect('/parent');
    }

    public function data()
    {
        $parents = $this->userRepository->getParentsAndStudents()
            ->map(function ($parent) {
                $students = '';
                foreach ($parent->student_parent as $item) {
                    foreach ($item->students as $student) {
                        $students .= $student->full_name.', ';
                    }
                }

                return [
                    'id' => $parent->id,
                    'parent' => $parent->full_name,
                    'student' => ($students != '') ? rtrim($students, ', ') : '',
                ];
            });

        return Datatables::make($parents)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'parent.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/parent/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                                <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                   @endif
                                   <a href="{{ url(\'/parent/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                   @if(Sentinel::getUser()->inRole(\'super_admin\') || Sentinel::getUser()->inRole(\'admin_super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'parent.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/parent/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                   @endif')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function getImport()
    {
        $title = trans('parent.import_parents');

        return view('parent.import', compact('title'));
    }

    public function postImport(ImportRequest $request)
    {
        $title = trans('parent.import_parents');

        ExcelfileValidator::validate($request);

        $reader = $this->excelRepository->load($request->file('file'));

        $parents = $reader->all()->map(function ($row) {
            if (filter_var(trim($row->student_email), FILTER_VALIDATE_EMAIL)) {
                return [
                    'student_email' => trim($row->student_email),
                    'first_name'    => trim($row->first_name),
                    'last_name'     => trim($row->last_name),
                    'email'         => trim($row->email),
                    'password'      => trim($row->password),
                    'mobile'        => trim($row->mobile),
                    'fax'           => trim($row->fax),
                    'birth_date'    => trim($row->birth_date),
                    'birth_place'   => trim($row->birth_place),
                    'address'       => trim($row->address),
                    'gender'        => trim($row->gender),
                ];
            } else {
                return null;
            }
        });

        return view('parent.import_list', compact('parents', 'title'));
    }

    public function finishImport(Request $request)
    {
        foreach ($request->import as $item) {
            $import_data = [
                'student_email'=>$request->student_email[$item],
                'first_name'=>$request->first_name[$item],
                'last_name'=>$request->last_name[$item],
                'email'=>$request->email[$item],
                'password'=>$request->password[$item],
                'mobile'=>$request->mobile[$item],
                'fax'=>$request->fax[$item],
                'birth_date'=>$request->birth_date[$item],
                'birth_place'=>$request->birth_place[$item],
                'address'=>$request->address[$item],
                'gender'=>$request->gender[$item],
            ];
            $this->parentStudentRepository->create($import_data);
        }

        return redirect('/parent');
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/parents.xlsx'));
    }

    public function export()
    {
        $parents = $this->userRepository->getParentsAndStudents()
                                        ->map(function ($parent) {
                                            $students = '';
                                            foreach ($parent->student_parent as $item) {
                                                foreach ($item->students as $student) {
                                                    $students .= $student->full_name.', ';
                                                }
                                            }

                                            return [
                                                'Parent email' => $parent->email,
                                                'Parent' => $parent->full_name,
                                                'Student' => ($students != '') ? rtrim($students, ', ') : '',
                                            ];
                                        })->toArray();

        Excel::create(trans('parent.parent'), function ($excel) use ($parents) {
            $excel->sheet(trans('parent.parent'), function ($sheet) use ($parents) {
                $sheet->fromArray($parents, null, 'A1', true);
            });
        })->export('csv');
    }
}
