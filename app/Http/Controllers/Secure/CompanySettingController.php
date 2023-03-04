<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\Settings;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\CompanySettingRequest;
use App\Http\Requests\Secure\CompanySettingsRequest;
use App\Http\Requests\Secure\SettingRequest;
use App\Repositories\EmployeeRepository;
use App\Models\Company;
use App\Models\Option;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends SecureController

{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    public function __construct(
        EmployeeRepository $employeeRepository,
    )
    {

        parent::__construct();
        $this->employeeRepository = $employeeRepository;

        view()->share('type', 'company_setting');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('settings.settings');

        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Employee', 0)
            ->toArray();

        $options = Option::all()->flatten()->groupBy('category')->map(function ($grp) {
            return $grp->pluck('value', 'title');
        });

        $opts = Option::all()->flatten()->groupBy('category')->map(function ($grp) {
            return $grp->map(function ($opt) {
                return [
                    'text' => $opt->value,
                    'id' => $opt->title,
                ];
            })->values();
        });
        $self_registration_role = [
            //'student' => 'Student',
            'visitor' => 'Visitor',
            'applicant' => 'Applicant',
        ];

        $sms_drivers = Option::where('category', 'sms_driver')->get()->map(function ($grp) {
            return [
                'text' => $grp->value,
                'id' => $grp->title,
            ];
        })->pluck('text', 'id');

        $themes = [0 => trans('settings.custom_theme')] + Theme::pluck('name', 'id')->toArray();
        $company = Company::find(session('current_company'));

        return view('company_setting.index', compact('title', 'employees',
            'options', 'opts', 'self_registration_role', 'themes', 'sms_drivers', 'company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SettingRequest|Request $request
     * @param Setting $setting
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function store(CompanySettingRequest $request)
    {
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/site/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture, 100, 100);

            $request->merge(['logo' => $picture]);
        }
        if ($request->hasFile('visitor_card_background_file') != '') {
            $file = $request->file('visitor_card_background_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/visitor_card/';
            $file->move($destinationPath, $picture);

            $request->merge(['visitor_card_background' => $picture]);
        }
        if ($request->hasFile('login_file')) {
            $file = $request->file('login_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/site/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture, 500, 500);

            $request->merge(['login' => $picture]);
        }

        $request->date_format = $request->date_format_custom;
        $request->time_format = $request->time_format_custom;
        if ($request->date_format == '') {
            $request->date_format = 'd.m.Y';
        }
        if ($request->time_format == '') {
            $request->time_format = 'H:i';
        }
        $request->merge([
            'jquery_date' => $this->dateformat_PHP_to_jQueryUI($request->date_format),
            'jquery_date_time' => $this->dateformat_PHP_to_jQueryUI($request->date_format.' '.$request->time_format),
        ]);
        foreach ($request->except('_token', 'login_file', 'logo_file', 'date_format_custom', 'time_format_custom', 'visitor_card_background_file')
                 as $key => $value) {
            CompanySettings::set($key, $value);
        }
        $this->makeFrontendTheme();
        $this->makeBackendTheme();

        return redirect()->back();
    }





    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(CompanySettingsRequest $request, Company $company)
    {
        try {
            DB::transaction(function () use ($request, $company) {
                if ($request->hasFile('student_card_background_file') != '') {
                    $file = $request->file('student_card_background_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/student_card/';
                    $file->move($destinationPath, $picture);
                    $company->student_card_background = $picture;
                }
                if ($request->hasFile('photo_file') != '') {
                    $file = $request->file('photo_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/school_photo/';
                    $file->move($destinationPath, $picture);
                    $company->photo = $picture;
                }
                $company->update($request->except('student_card_background_file', 'photo_file'));
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Company Updated Successfully</div>');
    }


    public function getThemeColors(Theme $theme)
    {
        return $theme->toArray();
    }

    private function makeBackendTheme()
    {
        $params = ['menu_bg_color', 'menu_active_bg_color', 'menu_active_border_right_color', 'menu_color', 'menu_active_color'];
        $theme_option = Option::where('category', 'theme_backend')->first();
        $theme = $theme_option->value;
        foreach ($params as $item) {
            $theme = str_replace('#'.$item.'#', Settings::get($item), $theme);
        }
        Storage::disk('public')->put('css/custom_colors.css', $theme);
    }

    private function makeFrontendTheme()
    {
        $params = ['frontend_bg_color', 'frontend_text_color', 'frontend_link_color', 'frontend_menu_bg_color'];
        $theme_option = Option::where('category', 'theme_frontend')->first();
        $theme = $theme_option->value;
        foreach ($params as $item) {
            $theme = str_replace('#'.$item.'#', Settings::get($item), $theme);
        }
        Storage::disk('public')->put('css/custom_frontend_colors.css', $theme);
    }
}
