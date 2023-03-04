<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ApplicationTokenRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Models\ApplicationToken;
use App\Repositories\SectionRepository;
use App\Models\SchoolDirection;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use function App\Helpers\randomString;

class ApplicationTokenController extends SecureController
{

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * DirectionController constructor.
     *
     * @param SectionRepository $sectionRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        SectionRepository $sectionRepository
    ) {

        parent::__construct();

        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'application_token');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('level.levels');
        $applicationTokens = ApplicationToken::where('company_id', session('current_company'))->get();
        return view('application_token.index', compact('title', 'applicationTokens'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('level.new');

        $token = rand(10000, 90000);

        return view('layouts.create', compact('title', 'token'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(ApplicationTokenRequest $request)
    {
        $applicationToken = new ApplicationToken($request->all());
        $applicationToken->company_id = session('current_company');
        $applicationToken->save();

        return redirect('/application_token');
    }

    /**
     * Display the specified resource.
     *
     * @param ApplicationToken $application_token
     * @return Response
     */
    public function show(ApplicationToken $application_token)
    {
        $title = trans('level.details');
        $action = 'show';
        return view('layouts.show', compact('application_token', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Level $applicationToken
     * @return Response
     */
    public function edit(ApplicationToken $application_token)
    {
        $title = trans('level.edit');

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'application_token', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param ApplicationToken $application_token
     * @return Response
     */
    public function update(ApplicationTokenRequest $request, ApplicationToken $application_token)
    {
        $application_token->update($request->all());
        return redirect('/application_token');
    }

    public function delete(ApplicationToken $application_token)
    {
        $title = trans('level.delete');
        return view('application_token.delete', compact('application_token', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Level $application_token
     * @return Response
     */
    public function destroy(ApplicationToken $application_token)
    {
        $application_token->delete();
        return redirect('/application_token');
    }


}
