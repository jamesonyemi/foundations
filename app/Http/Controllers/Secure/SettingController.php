<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\SettingRequest;
use App\Models\Option;
use App\Models\Theme;
use Illuminate\Support\Facades\Storage;

class SettingController extends SecureController
{
    public function __construct()
    {
        parent::__construct();

        view()->share('type', 'setting');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('settings.settings');
        $max_upload_file_size = [
            '1000' => '1MB',
            '2000' => '2MB',
            '3000' => '3MB',
            '4000' => '4MB',
            '5000' => '5MB',
            '6000' => '6MB',
            '7000' => '7MB',
            '8000' => '8MB',
            '9000' => '9MB',
            '10000' => '10MB',
        ];
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

        return view('setting.index', compact('title', 'max_upload_file_size',
            'options', 'opts', 'self_registration_role', 'themes', 'sms_drivers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SettingRequest|Request $request
     * @param Setting $setting
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function store(SettingRequest $request)
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
            Settings::set($key, $value);
        }
        $this->makeFrontendTheme();
        $this->makeBackendTheme();

        return redirect()->back();
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
