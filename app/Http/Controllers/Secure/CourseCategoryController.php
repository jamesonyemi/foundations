<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\CourseCategoryRequest;
use App\Models\CourseCategory;
use App\Models\CourseCategoryProgram;
use App\Models\CourseCategorySection;
use App\Repositories\SectionRepository;
use App\Repositories\CourseCategorySectionRepository;
use App\Repositories\CourseCategoryDirectionRepository;
use App\Repositories\DirectionRepository;
use App\Models\SchoolDirection;
use App\Repositories\LevelRepository;
use App\Repositories\CourseCategoryRepository;
use App\Helpers\Settings;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class CourseCategoryController extends SecureController
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;

     /**
     * @var LevelRepository
     */
    private $courseCategorySectionRepository;

     /**
     * @var LevelRepository
     */
    private $courseCategoryDirectionRepository;


    /**
     * @var LevelRepository
     */
    private $directionRepository;

    /**
     * @var LevelRepository
     */
    private $courseCategoryRepository;
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
        CourseCategorySectionRepository $courseCategorySectionRepository,
        CourseCategoryDirectionRepository $courseCategoryDirectionRepository,
        DirectionRepository $directionRepository,
        CourseCategoryRepository $courseCategoryRepository,
        SectionRepository $sectionRepository
    ) {

        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->courseCategorySectionRepository = $courseCategorySectionRepository;
        $this->courseCategoryDirectionRepository = $courseCategoryDirectionRepository;
        $this->directionRepository = $directionRepository;
        $this->courseCategoryRepository = $courseCategoryRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'course_category');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('course_categories.Categories');
        $courseCategories = $this->courseCategoryRepository->getAllForSchool(session('current_company'))
            ->get();
        return view('course_category.index', compact('title', 'courseCategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('level.new');

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->toArray();

        $directions = $this->directionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->toArray();

        return view('layouts.create', compact('title', 'sections', 'directions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(CourseCategoryRequest $request)
    {
        $courseCategory = new CourseCategory($request->except('section_id', 'direction_id'));
        $courseCategory->company_id = session('current_company');
        $courseCategory->save();


        if (!empty($request['section_id'])) {

            CourseCategorySection::where('course_category_id', $courseCategory->id)->delete();

            foreach ($request['section_id'] as $section_id) {
                $CourseCategorySection = new CourseCategorySection();
                $CourseCategorySection->course_category_id = $courseCategory->id;
                $CourseCategorySection->section_id = $section_id;
                $CourseCategorySection->company_id = session('current_company');
                $CourseCategorySection->save();
            }
        }

        if (!empty($request['direction_id'])) {

            CourseCategoryProgram::where('course_category_id', $courseCategory->id)->delete();

            foreach ($request['direction_id'] as $direction_id) {
                $CourseCategoryProgram = new CourseCategoryProgram();
                $CourseCategoryProgram->course_category_id = $courseCategory->id;
                $CourseCategoryProgram->direction_id = $direction_id;
                $CourseCategoryProgram->company_id = session('current_company');
                $CourseCategoryProgram->save();
            }
        }

        return redirect('/course_category');
    }

    /**
     * Display the specified resource.
     *
     * @param Level $level
     * @return Response
     */
    public function show(CourseCategory $courseCategory)
    {
        $title = trans('level.details');
        $action = 'show';
        return view('layouts.show', compact('courseCategory', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Level $level
     * @return Response
     */
    public function edit(CourseCategory $courseCategory)
    {
        $title = trans('level.edit');

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->toArray();

        $directions = $this->directionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->toArray();

        $categorySections = $this->courseCategorySectionRepository->getAllForCourseCategory($courseCategory->id)
            ->get()
            ->pluck('section_id', 'section_id')
            ->toArray();

        $categorydirections = $this->courseCategoryDirectionRepository->getAllForCourseCategory($courseCategory->id)
            ->get()
            ->pluck('direction_id', 'direction_id')
            ->toArray();

        return view('layouts.edit', compact('title', 'courseCategory', 'sections', 'directions', 'categorySections', 'categorydirections'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Level $level
     * @return Response
     */
    public function update(CourseCategoryRequest $request, CourseCategory $courseCategory)
    {
        $courseCategory->update($request->except('section_id', 'direction_id'));

        if (!empty($request['section_id'])) {

            CourseCategorySection::where('course_category_id', $courseCategory->id)->delete();

            foreach ($request['section_id'] as $section_id) {
                $CourseCategorySection = new CourseCategorySection();
                $CourseCategorySection->course_category_id = $courseCategory->id;
                $CourseCategorySection->section_id = $section_id;
                $CourseCategorySection->company_id = session('current_company');
                $CourseCategorySection->save();
            }
        }

        if (!empty($request['direction_id'])) {

            CourseCategoryProgram::where('course_category_id', $courseCategory->id)->delete();

            foreach ($request['direction_id'] as $direction_id) {
                $CourseCategoryProgram = new CourseCategoryProgram();
                $CourseCategoryProgram->course_category_id = $courseCategory->id;
                $CourseCategoryProgram->direction_id = $direction_id;
                $CourseCategoryProgram->company_id = session('current_company');
                $CourseCategoryProgram->save();
            }
        }

        return redirect('/course_category');
    }

    public function delete(CourseCategory $courseCategory)
    {
        $title = trans('level.delete');
        return view('course_category.delete', compact('courseCategory', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Level $level
     * @return Response
     */
    public function destroy(CourseCategory $courseCategory)
    {
        $courseCategory->delete();
        return redirect('/course_category');
    }

    public function data()
    {
        $levels = $this->levelRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($level) {
                return [
                    'id' => $level->id,
                    'name' => $level->name,
                    'section' => $level->section->title,
                ];
            });

        return Datatables::make($levels)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.edit\', Sentinel::getUser()->permissions)))
										<a href="{{ url(\'/levels/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.show\', Sentinel::getUser()->permissions)))
                                    	<a href="{{ url(\'/levels/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     @endif
                                     @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.delete\', Sentinel::getUser()->permissions)))
                                     	<a href="{{ url(\'/levels/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
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
}
