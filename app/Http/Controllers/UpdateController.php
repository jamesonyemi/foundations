<?php

namespace App\Http\Controllers;

use App\Helpers\EnvatoValidator;
use App\Helpers\NatureDevValidator;
use App\Helpers\Settings;
use App\Models\Option;
use App\Models\Permission;
use App\Models\Theme;
use App\Models\Version;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Sentinel;

class UpdateController extends Controller
{
    private $versions = [];

    public function __construct()
    {
        $this->versions = ['3.4' => 'update34', '3.5' => 'update35', '3.7' => 'update37',
            '3.8' => 'update38', '3.12' => 'update312', '3.13' => 'update313',
            '4.0' => 'update40', '4.1' => 'update41', '4.4' => 'update44', '5.0' => 'update50',
            '5.4' => 'update54', '5.6'=>'update56',  '5.8'=>'update58', '6.0'=>'update60', '6.2'=> 'update62',
            '6.4' => 'update64', ];
    }

    public function index($version, Request $request)
    {
        if (Settings::get('envato') == 'no') {
            if (NatureDevValidator::is_connected()) {
                $response = NatureDevValidator::update($request);
                if ($response['status'] == 'success') {
                    $steps = [
                        'welcome' => 'active',
                    ];

                    return view('update.start', compact('steps', 'version'));
                }

                return redirect('verify');
            }
        } else {
            if (EnvatoValidator::is_connected()) {
                $response = EnvatoValidator::update($request);
                if ($response['status'] == 'success') {
                    $steps = [
                        'welcome' => 'active',
                    ];

                    return view('update.start', compact('steps', 'version'));
                }

                return redirect('verify');
            }
        }
        unlink(storage_path('installed'));

        return redirect()->back();
    }

    public function update($version)
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            $final_version = $this->updateToVersion($version);

            return redirect('update/'.$final_version.'/complete');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect('update/'.$version.'/error');
        }
    }

    public function complete($version)
    {
        $steps = [
            'welcome' => 'success_step',
            'complete' => 'active', ];

        return view('update.complete', compact('steps', 'version'));
    }

    public function error($version)
    {
        return view('update.error', compact('version'));
    }

    private function updateToVersion($current_version)
    {
        $work_update = false;
        foreach ($this->versions as $key => $value) {
            if ($work_update) {
                $this->$value();
            }
            if ($key == $current_version) {
                $work_update = true;
            }
        }
        $version_last = Version::first();
        if (isset($version_last)) {
            $version_last->version = config('app.version');
        } else {
            $version_last = new Version(['version' => config('app.version')]);
        }
        $version_last->save();

        return config('app.version');
    }

    //update methods
    public function update34()
    {
        //add attendance_type options
        Option::create([
            'category' => 'attendance_type',
            'company_id' => 0,
            'title' => 'Present',
            'value' => 'Present',
        ]);
        Option::create([
            'category' => 'attendance_type',
            'company_id' => 0,
            'title' => 'Absent',
            'value' => 'Absent',
        ]);
        Option::create([
            'category' => 'attendance_type',
            'company_id' => 0,
            'title' => 'Late',
            'value' => 'Late',
        ]);
        Option::create([
            'category' => 'attendance_type',
            'company_id' => 0,
            'title' => 'Late with excuse',
            'value' => 'Late with excuse',
        ]);
    }

    public function update35()
    {
        Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Accountant',
            'slug' => 'accountant',
        ]);
    }

    public function update37()
    {
        Option::create([
            'category' => 'attendance_type',
            'company_id' => 0,
            'title' => 'Not present',
            'value' => 'Not present',
        ]);
        Option::create([
            'category' => 'student_document_type',
            'company_id' => 0,
            'title' => 'Transfer certificate',
            'value' => 'Transfer certificate',
        ]);
        Option::create([
            'category' => 'staff_document_type',
            'company_id' => 0,
            'title' => 'Resume',
            'value' => 'Resume',
        ]);
    }

    public function update38()
    {
        //exam type
        Option::create([
            'category' => 'exam_type',
            'company_id' => 0,
            'title' => 'Oral exam',
            'value' => 'Oral exam',
        ]);
    }

    public function update312()
    {
        Option::create([
            'category' => 'sms_driver',
            'company_id' => 0,
            'title' => 'none',
            'value' => '...',
        ]);

        Option::create([
            'category' => 'sms_driver',
            'company_id' => 0,
            'title' => 'callfire',
            'value' => 'CallFire',
        ]);

        Option::create([
            'category' => 'sms_driver',
            'company_id' => 0,
            'title' => 'eztexting',
            'value' => 'EzTexting',
        ]);

        Option::create([
            'category' => 'sms_driver',
            'company_id' => 0,
            'title' => 'labsmobile',
            'value' => 'LabsMobile',
        ]);

        Option::create([
            'category' => 'sms_driver',
            'company_id' => 0,
            'title' => 'mozeo',
            'value' => 'Mozeo',
        ]);

        Option::create([
            'category' => 'sms_driver',
            'company_id' => 0,
            'title' => 'nexmo',
            'value' => 'Nexmo',
        ]);

        Option::create([
            'category' => 'sms_driver',
            'company_id' => 0,
            'title' => 'twilio',
            'value' => 'Twilio',
        ]);

        Option::create([
            'category' => 'sms_driver',
            'company_id' => 0,
            'title' => 'zenvia',
            'value' => 'Zenvia',
        ]);
    }

    public function update313()
    {
        $path = public_path().'/uploads/school_photo';
        File::makeDirectory($path, $mode = 0777, true, true);
    }

    public function update40()
    {
        $path = public_path().'/uploads/study_material';
        File::makeDirectory($path, $mode = 0777, true, true);
    }

    public function update41()
    {
        Option::create([
            'category' => 'book_category',
            'company_id' => 0,
            'title' => 'Books',
            'value' => 'books',
        ]);

        Option::create([
            'category' => 'book_category',
            'company_id' => 0,
            'title' => 'Journals',
            'value' => 'journals',
        ]);

        Option::create([
            'category' => 'book_category',
            'company_id' => 0,
            'title' => 'Newspapers',
            'value' => 'newspapers',
        ]);

        Option::create([
            'category' => 'book_category',
            'company_id' => 0,
            'title' => 'Magazines',
            'value' => 'magazines',
        ]);

        Option::create([
            'category' => 'borrowing_period',
            'company_id' => 0,
            'title' => 'Internal use',
            'value' => '0',
        ]);
        Option::create([
            'category' => 'borrowing_period',
            'company_id' => 0,
            'title' => 'Overnight',
            'value' => '1',
        ]);
        Option::create([
            'category' => 'borrowing_period',
            'company_id' => 0,
            'title' => 'Short loan',
            'value' => '3',
        ]);
        Option::create([
            'category' => 'borrowing_period',
            'company_id' => 0,
            'title' => 'Long loan',
            'value' => '7',
        ]);
    }

    public function update44()
    {
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Notice',
            'group_slug' => 'notice',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Notice',
            'group_slug' => 'notice',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Notice',
            'group_slug' => 'notice',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Notice',
            'group_slug' => 'notice',
            'name' => 'edit',
        ]);

        Permission::create([
            'role_id' => 2,
            'group_name' => 'Diary',
            'group_slug' => 'diary',
            'name' => 'show',
        ]);
        //sections
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Department',
            'group_slug' => 'section',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Department',
            'group_slug' => 'section',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Sections',
            'group_slug' => 'section',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Department',
            'group_slug' => 'section',
            'name' => 'edit',
        ]);

        //student_groups
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Student group',
            'group_slug' => 'student_group',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Student group',
            'group_slug' => 'student_group',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Student group',
            'group_slug' => 'student_group',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Student group',
            'group_slug' => 'student_group',
            'name' => 'edit',
        ]);

        //students
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Students',
            'group_slug' => 'student',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Students',
            'group_slug' => 'student',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Students',
            'group_slug' => 'student',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Students',
            'group_slug' => 'student',
            'name' => 'edit',
        ]);

        //student_final marks
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Student final marks',
            'group_slug' => 'student_final_mark',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Student final marks',
            'group_slug' => 'student_final_mark',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Student final marks',
            'group_slug' => 'student_final_mark',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Student final marks',
            'group_slug' => 'student_final_mark',
            'name' => 'edit',
        ]);

        //student attendances
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Student attendances',
            'group_slug' => 'student_attendances_admin',
            'name' => 'show',
        ]);

        //parents
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Parents',
            'group_slug' => 'parent',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Parents',
            'group_slug' => 'parent',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Parents',
            'group_slug' => 'parent',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Parents',
            'group_slug' => 'parent',
            'name' => 'edit',
        ]);

        //human resources
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Human resources',
            'group_slug' => 'human_resource',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Human resources',
            'group_slug' => 'human_resource',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Human resources',
            'group_slug' => 'human_resource',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Human resources',
            'group_slug' => 'human_resource',
            'name' => 'edit',
        ]);

        //teacher
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Teachers',
            'group_slug' => 'teacher',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Teachers',
            'group_slug' => 'teacher',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Teachers',
            'group_slug' => 'teacher',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Teachers',
            'group_slug' => 'teacher',
            'name' => 'edit',
        ]);
        //librarians
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Librarians',
            'group_slug' => 'librarian',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Librarians',
            'group_slug' => 'librarian',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Librarians',
            'group_slug' => 'librarian',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Librarians',
            'group_slug' => 'librarian',
            'name' => 'edit',
        ]);
        //accountants
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Accountants',
            'group_slug' => 'accountant',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Accountants',
            'group_slug' => 'accountant',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Accountants',
            'group_slug' => 'accountant',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Accountants',
            'group_slug' => 'accountant',
            'name' => 'edit',
        ]);
        //visitors
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Visitors',
            'group_slug' => 'visitor',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Visitors',
            'group_slug' => 'visitor',
            'name' => 'delete',
        ]);
        //scholarships
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Scholarships',
            'group_slug' => 'scholarship',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Scholarships',
            'group_slug' => 'scholarship',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Scholarships',
            'group_slug' => 'scholarship',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Scholarships',
            'group_slug' => 'scholarship',
            'name' => 'edit',
        ]);
        //salary
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Staff salary',
            'group_slug' => 'salary',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Staff salary',
            'group_slug' => 'salary',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Staff salary',
            'group_slug' => 'salary',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Staff salary',
            'group_slug' => 'salary',
            'name' => 'edit',
        ]);
        //fee_category
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Fee category',
            'group_slug' => 'fee_category',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Fee category',
            'group_slug' => 'fee_category',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Fee category',
            'group_slug' => 'fee_category',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Fee category',
            'group_slug' => 'fee_category',
            'name' => 'edit',
        ]);
        //sms_message
        Permission::create([
            'role_id' => 2,
            'group_name' => 'SMS message',
            'group_slug' => 'sms_message',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'SMS message',
            'group_slug' => 'sms_message',
            'name' => 'create',
        ]);
        //dormitory
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory',
            'group_slug' => 'dormitory',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory',
            'group_slug' => 'dormitory',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory',
            'group_slug' => 'dormitory',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory',
            'group_slug' => 'dormitory',
            'name' => 'edit',
        ]);
        //dormitory rooms
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory rooms',
            'group_slug' => 'dormitoryroom',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory rooms',
            'group_slug' => 'dormitoryroom',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory rooms',
            'group_slug' => 'dormitoryroom',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory rooms',
            'group_slug' => 'dormitoryroom',
            'name' => 'edit',
        ]);
        //dormitory beds
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory beds',
            'group_slug' => 'dormitorybed',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory beds',
            'group_slug' => 'dormitorybed',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory beds',
            'group_slug' => 'dormitorybed',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Dormitory beds',
            'group_slug' => 'dormitorybed',
            'name' => 'edit',
        ]);
        //transportation
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Transportation',
            'group_slug' => 'transportation',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Transportation',
            'group_slug' => 'transportation',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Transportation',
            'group_slug' => 'transportation',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Transportation',
            'group_slug' => 'transportation',
            'name' => 'edit',
        ]);
        //invoice
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Invoice',
            'group_slug' => 'invoice',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Invoice',
            'group_slug' => 'invoice',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Invoice',
            'group_slug' => 'invoice',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Invoice',
            'group_slug' => 'invoice',
            'name' => 'edit',
        ]);
        //debtor
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Debtor',
            'group_slug' => 'debtor',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Debtor',
            'group_slug' => 'debtor',
            'name' => 'create',
        ]);
        //payment
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Payments',
            'group_slug' => 'payment',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Payments',
            'group_slug' => 'payment',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Payments',
            'group_slug' => 'payment',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Payments',
            'group_slug' => 'payment',
            'name' => 'edit',
        ]);
        //holidays
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Holidays',
            'group_slug' => 'holiday',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Holidays',
            'group_slug' => 'holiday',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Holidays',
            'group_slug' => 'holiday',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Holidays',
            'group_slug' => 'holiday',
            'name' => 'edit',
        ]);
    }

    public function update50()
    {
        Settings::set('menu_bg_color', '#333333');
        Settings::set('menu_active_bg_color', '#222222');
        Settings::set('menu_active_border_right_color', '#2ea2cc');
        Settings::set('menu_color', '#ffffff');
        Settings::set('menu_active_color', '#ffffff');
        Settings::set('frontend_menu_bg_color', '#333333');
        Settings::set('frontend_bg_color', '#ffffff');
        Settings::set('frontend_text_color', '#333333');
        Settings::set('frontend_link_color', '#2ea2cc');
        Settings::set('rtl_support', 'no');

        Theme::create([
            'name' => 'Default',
            'menu_bg_color' => '#333333',
            'menu_active_bg_color' => '#222222',
            'menu_active_border_right_color' => '#2ea2cc',
            'menu_color' => '#ffffff',
            'menu_active_color' => '#ffffff',
            'frontend_menu_bg_color' => '#333333',
            'frontend_bg_color' => '#ffffff',
            'frontend_text_color' => '#333333',
            'frontend_link_color' => '#2ea2cc',
        ]);

        Theme::create([
            'name' => 'Light',
            'menu_bg_color' => '#e5e5e5',
            'menu_active_bg_color' => '#999999',
            'menu_active_border_right_color' => '#04a4cc',
            'menu_color' => '#333333',
            'menu_active_color' => '#333333',
            'frontend_menu_bg_color' => '#e5e5e5',
            'frontend_bg_color' => '#ffffff',
            'frontend_text_color' => '#999999',
            'frontend_link_color' => '#04a4cc',
        ]);

        Theme::create([
            'name' => 'Blue',
            'menu_bg_color' => '#4796b3',
            'menu_active_bg_color' => '#096484',
            'menu_active_border_right_color' => '#74b6ce',
            'menu_color' => '#ffffff',
            'menu_active_color' => '#ffffff',
            'frontend_menu_bg_color' => '#4796b3',
            'frontend_bg_color' => '#ffffff',
            'frontend_text_color' => '#333333',
            'frontend_link_color' => '#4796b3',
        ]);

        Theme::create([
            'name' => 'Coffee',
            'menu_bg_color' => '#59524c',
            'menu_active_bg_color' => '#c7a589',
            'menu_active_border_right_color' => '#9ea476',
            'menu_color' => '#ffffff',
            'menu_active_color' => '#ffffff',
            'frontend_menu_bg_color' => '#59524c',
            'frontend_bg_color' => '#ffffff',
            'frontend_text_color' => '#59524c',
            'frontend_link_color' => '#c7a589',
        ]);

        Theme::create([
            'name' => 'Ectoplasm',
            'menu_bg_color' => '#413256',
            'menu_active_bg_color' => '#a3b745',
            'menu_active_border_right_color' => '#d46f15',
            'menu_color' => '#ffffff',
            'menu_active_color' => '#ffffff',
            'frontend_menu_bg_color' => '#413256',
            'frontend_bg_color' => '#ffffff',
            'frontend_text_color' => '#a3b745',
            'frontend_link_color' => '#d46f15',
        ]);

        Theme::create([
            'name' => 'Midnight',
            'menu_bg_color' => '#363b3f',
            'menu_active_bg_color' => '#e14d43',
            'menu_active_border_right_color' => '#69a8bb',
            'menu_color' => '#ffffff',
            'menu_active_color' => '#ffffff',
            'frontend_menu_bg_color' => '#363b3f',
            'frontend_bg_color' => '#ffffff',
            'frontend_text_color' => '#363b3f',
            'frontend_link_color' => '#69a8bb',
        ]);

        Theme::create([
            'name' => 'Ocean',
            'menu_bg_color' => '#738e96',
            'menu_active_bg_color' => '#9ebaa0',
            'menu_active_border_right_color' => '#aa9d88',
            'menu_color' => '#ffffff',
            'menu_active_color' => '#ffffff',
            'frontend_menu_bg_color' => '#738e96',
            'frontend_bg_color' => '#ffffff',
            'frontend_text_color' => '#738e96',
            'frontend_link_color' => '#9ebaa0',
        ]);

        Theme::create([
            'name' => 'Sunrise',
            'menu_bg_color' => '#cf4944',
            'menu_active_bg_color' => '#dd823b',
            'menu_active_border_right_color' => '#ccaf0b',
            'menu_color' => '#ffffff',
            'menu_active_color' => '#ffffff',
            'frontend_menu_bg_color' => '#cf4944',
            'frontend_bg_color' => '#ffffff',
            'frontend_text_color' => '#cf4944',
            'frontend_link_color' => '#dd823b',
        ]);

        Option::create([
            'category' => 'theme_backend',
            'company_id' => 0,
            'title' => 'Backend Theme',
            'value' => '.left_col,.nav-md ul.nav.child_menu li:before,.nav-sm ul.nav.child_menu,.nav.side-menu>li.active>a,.nav_menu,.panel_toolbox>li>a:hover,.top_nav .nav .open>a,.top_nav .nav .open>a:focus,.top_nav .nav .open>a:hover,.top_nav .nav>li>a:focus,.top_nav .nav>li>a:hover{background:#menu_bg_color#!important}.nav-sm .nav.child_menu li.active,.nav-sm .nav.side-menu li.active-sm{border-right:5px solid #menu_active_bg_color#!important}.nav-sm>.nav.side-menu>li.active-sm>a{color:#menu_active_bg_color#!important}.main_menu span.fa,.profile_info h2,.profile_info span{color:#menu_color#!important}.nav_menu{border-bottom:1px solid #menu_color#!important}.navbar-brand,.navbar-nav>li>a{color:#menu_color#!important}a{color:#ffffff!important}.nav.side-menu>li>a:hover{color:#menu_active_color#!important}.nav li li.current-page a,.nav.child_menu li li a.active,.nav.child_menu li li a:hover,.nav.child_menu>li>a,.nav.navbar-nav>li>a,.nav.side-menu>li>a,.navbar-brand,.navbar-nav>li>a{color:#menu_color#!important}.nav-md ul.nav.child_menu li:after{border-left:1px solid #menu_bg_color#!important}.nav.side-menu>li.active,.nav.side-menu>li.current-page{border-right:5px solid #menu_active_border_right_color#!important}.dropdown-menu,.paging_full_numbers a.paginate_active,.paging_full_numbers a.paginate_button{border:1px solid!important}.nav.top_menu>li>a{color:#menu_bg_color#!important}.nav.child_menu>li>a,.pagination.pagination-split li a,.panel_toolbox>li>a{color:#menu_color#!important}.paging_full_numbers a.paginate_button{background-color:#menu_color#!important}.paging_full_numbers a.paginate_button:hover{background-color:#menu_active_color#!important}.paging_full_numbers a.paginate_active{background-color:#menu_bg_color#!important}table.display tr.even.row_selected td{background-color:#menu_active_color#!important}table.display tr.odd.row_selected td{background-color:#menu_color#!important}.dropdown-menu>li>a{color:#menu_bg_color#!important}.navbar-nav .open .dropdown-menu{background:0 0!important;border:1px solid!important}.nav_title{background:0 0!important}body{background-color:transparent!important}a.tabs_settings{color:#menu_bg_color#!important}',
        ]);

        Option::create([
            'category' => 'theme_frontend',
            'company_id' => 0,
            'title' => 'Frontend Theme',
            'value' => 'a:focus,a:hover,body,h1,h2,h3,h4,h5,h6{color:#frontend_text_color#!important}.navbar,.navbar-inverse{border-color:#frontend_menu_bg_color#!important}body{background-color:#frontend_bg_color#!important}.navbar,.navbar-inverse,.navbar-inverse .navbar-nav>.active>a,vbar .navbar-nav>.active>a{background:#ffffff!important}a{color:#ffffff!important}.navbar .navbar-brand{color:#frontend_text_color#!important}.navbar .navbar-brand i{color:#frontend_link_color#!important}.navbar-inverse .navbar-brand{color:#frontend_text_color#!important}.navbar-inverse .navbar-brand i{color:#frontend_link_color#!important}',
        ]);
    }

    public function update54()
    {
        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'CAD',
            'value' => 'CAD',
        ]);
        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'GBP',
            'value' => 'GBP',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'AUD',
            'value' => 'AUD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'ANG',
            'value' => 'ANG',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'AOA',
            'value' => 'AOA',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'ARS',
            'value' => 'ARS',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'BBD',
            'value' => 'BBD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'BGL',
            'value' => 'BGL',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'BHD',
            'value' => 'BHD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'BND',
            'value' => 'BND',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'BRL',
            'value' => 'BRL',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'CHF',
            'value' => 'CHF',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'CLF',
            'value' => 'CLF',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'CLP',
            'value' => 'CLP',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'CNY',
            'value' => 'CNY',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'COP',
            'value' => 'COP',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'CRC',
            'value' => 'CRC',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'CZK',
            'value' => 'CZK',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'DKK',
            'value' => 'DKK',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'EEK',
            'value' => 'EEK',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'EGP',
            'value' => 'EGP',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'FJD',
            'value' => 'FJD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'GTQ',
            'value' => 'GTQ',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'HKD',
            'value' => 'HKD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'HRK',
            'value' => 'HRK',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'HUF',
            'value' => 'HUF',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'IDR',
            'value' => 'IDR',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'ILS',
            'value' => 'ILS',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'INR',
            'value' => 'INR',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'JPY',
            'value' => 'JPY',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'KES',
            'value' => 'KES',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'KRW',
            'value' => 'KRW',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'KWD',
            'value' => 'KWD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'KYD',
            'value' => 'KYD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'LTL',
            'value' => 'LTL',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'LVL',
            'value' => 'LVL',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'MVR',
            'value' => 'MVR',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'MXN',
            'value' => 'MXN',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'MYR',
            'value' => 'MYR',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'NGN',
            'value' => 'NGN',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'NOK',
            'value' => 'NOK',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'NZD',
            'value' => 'NZD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'OMR',
            'value' => 'OMR',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'PEN',
            'value' => 'PEN',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'PHP',
            'value' => 'PHP',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'PLN',
            'value' => 'PLN',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'QAR',
            'value' => 'QAR',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'RON',
            'value' => 'RON',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'RUB',
            'value' => 'RUB',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'SAR',
            'value' => 'SAR',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'SEK',
            'value' => 'SEK',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'SGD',
            'value' => 'SGD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'THB',
            'value' => 'THB',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'TTD',
            'value' => 'TTD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'TWD',
            'value' => 'TWD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'UAH',
            'value' => 'UAH',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'VEF',
            'value' => 'VEF',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'VND',
            'value' => 'VND',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'XCD',
            'value' => 'XCD',
        ]);

        Option::create([
            'category' => 'currency',
            'company_id' => 0,
            'title' => 'ZAR',
            'value' => 'ZAR',
        ]);

        Option::create([
            'category' => 'report_type',
            'company_id' => 0,
            'title' => 'Students cards (all marks and attendances)',
            'value' => 'student_cards',
        ]);
    }

    public function update56()
    {
        Option::create([
            'category' => 'sms_driver',
            'company_id' => 0,
            'title' => 'bulk_sms',
            'value' => 'Bulk SMS',
        ]);
    }

    public function update58()
    {
        //Company exams
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Company exams',
            'group_slug' => 'school_exam',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Company exams',
            'group_slug' => 'school_exam',
            'name' => 'create',
        ]);
    }

    public function update60()
    {
        Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Supplier',
            'slug' => 'supplier',
        ]);

        Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Kitchen admin',
            'slug' => 'kitchen_admin',
        ]);

        Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Kitchen staff',
            'slug' => 'kitchen_staff',
        ]);

        //supplier
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Supplier',
            'group_slug' => 'supplier',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Supplier',
            'group_slug' => 'supplier',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Supplier',
            'group_slug' => 'supplier',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Supplier',
            'group_slug' => 'supplier',
            'name' => 'edit',
        ]);

        //kitchen admin
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Kitchen admin',
            'group_slug' => 'kitchen_admin',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Kitchen admin',
            'group_slug' => 'kitchen_admin',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Kitchen admin',
            'group_slug' => 'kitchen_admin',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Kitchen admin',
            'group_slug' => 'kitchen_admin',
            'name' => 'edit',
        ]);

        //kitchen staff
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Kitchen staff',
            'group_slug' => 'kitchen_staff',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Kitchen staff',
            'group_slug' => 'kitchen_staff',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Kitchen staff',
            'group_slug' => 'kitchen_staff',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Kitchen staff',
            'group_slug' => 'kitchen_staff',
            'name' => 'edit',
        ]);

        //meal type
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Meal type',
            'group_slug' => 'meal_type',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Meal type',
            'group_slug' => 'meal_type',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Meal type',
            'group_slug' => 'meal_type',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Meal type',
            'group_slug' => 'meal_type',
            'name' => 'edit',
        ]);

        //meal
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Meal',
            'group_slug' => 'meal',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Meal',
            'group_slug' => 'meal',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Meal',
            'group_slug' => 'meal',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Meal',
            'group_slug' => 'meal',
            'name' => 'edit',
        ]);

        //teacher_duty
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Teacher duty',
            'group_slug' => 'teacher_duty',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Teacher duty',
            'group_slug' => 'teacher_duty',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Teacher duty',
            'group_slug' => 'teacher_duty',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Teacher duty',
            'group_slug' => 'teacher_duty',
            'name' => 'edit',
        ]);
    }

    public function update62()
    {
        Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Doorman',
            'slug' => 'doorman',
        ]);

        Permission::create([
            'role_id' => 2,
            'group_name' => 'Doorman',
            'group_slug' => 'doorman',
            'name' => 'show',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Doorman',
            'group_slug' => 'doorman',
            'name' => 'create',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Doorman',
            'group_slug' => 'doorman',
            'name' => 'delete',
        ]);
        Permission::create([
            'role_id' => 2,
            'group_name' => 'Doorman',
            'group_slug' => 'doorman',
            'name' => 'edit',
        ]);

        Permission::create([
            'role_id' => 2,
            'group_name' => 'Read school users mails',
            'group_slug' => 'all_mails',
            'name' => 'show',
        ]);
    }

    public function update64()
    {
        Option::create([
            'category' => 'sms_driver',
            'company_id' => 0,
            'title' => 'msg91',
            'value' => 'MSG91',
        ]);
    }
}
