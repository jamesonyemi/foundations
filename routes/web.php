<?php


use App\Http\Controllers\Secure\FaqCategoryController;
use App\Http\Controllers\Secure\FaqController;
use App\Http\Controllers\Secure\FeaturesController;
use App\Http\Controllers\Frontend;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\Secure;
use App\Http\Controllers\UpdateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



/****************   Model binding into route **************************/

Route::model('teacher_user', \App\Models\User::class);
Route::model('contact_request', \App\Models\User::class);
Route::model('human_resource', \App\Models\User::class);
Route::model('school_admin', \App\Models\User::class);
Route::model('librarian_user', \App\Models\User::class);
Route::model('doorman_user', \App\Models\User::class);
Route::model('student_user', \App\Models\User::class);
Route::model('parent_user', \App\Models\User::class);
Route::model('visitor', \App\Models\User::class);
Route::model('applicant_user', \App\Models\User::class);
Route::model('accountant', \App\Models\User::class);


Route::pattern('slug', '[a-z0-9-]+');
Route::pattern('version', '[0-9.]+');

/******************   APP routes  ********************************/

//default route - homepage for all roles
Route::get('/', [Secure\SecureController::class, 'showHome']);
Route::get('/confirm-delete-page/{project_id}/show', [Secure\SecureController::class, 'showConfirmDeletePage']);
Route::post('/delete', [Secure\SecureController::class, 'destroy'])->name("delete-project");
Route::get('/attendances/ajax_load_calender', [Secure\SecureController::class, 'ajax_load_calender'])->name('admin.attendance.ajax_load_calender');
Route::post('/ajax_dashboard_data', [Secure\SecureController::class, 'ajax_dashboard_data'])->name('admin.ajax_dashboard_data');
Route::post('/ajax_dashboard_sector_kpi_data', [Secure\SecureController::class, 'ajax_dashboard_sector_kpi_data'])->name('admin.ajax_dashboard_sector_kpi_data');
Route::post('/ajax_group_dashboard_data', [Secure\SecureController::class, 'ajax_group_dashboard_data'])->name('admin.ajax_group_dashboard_data');
Route::post('/ajax_sector_dashboard_data', [Secure\SecureController::class, 'ajax_sector_dashboard_data'])->name('admin.ajax_sector_dashboard_data');
Route::post('/ajax_company_dashboard_data', [Secure\SecureController::class, 'ajax_company_dashboard_data'])->name('admin.ajax_company_dashboard_data');
Route::post('/ajax_department_dashboard_data', [Secure\SecureController::class, 'ajax_department_dashboard_data'])->name('admin.ajax_department_dashboard_data');
Route::post('/ajax_personal_dashboard_data', [Secure\SecureController::class, 'ajax_personal_dashboard_data'])->name('admin.ajax_personal_dashboard_data');

Route::post('events', [Secure\SecureController::class, 'events']);
Route::get('language/setlang/{slug}', [Secure\LanguageController::class, 'setlang']);

//route after user login into system
Route::get('signin', [Secure\AuthController::class, 'getSignin'])->name('login');
Route::post('signin', [Secure\AuthController::class, 'postSignin']);
Route::get('signup', [Secure\AuthController::class, 'getSignup']);
Route::post('signup', [Secure\AuthController::class, 'postSignup']);
Route::get('apply/{school}/{year}', [Secure\AuthController::class, 'getApply']);
Route::post('apply', [Secure\AuthController::class, 'postApply']);

Route::get('schoolApply', [Secure\AuthController::class, 'getSchoolApply']);
Route::post('schoolApply', [Secure\AuthController::class, 'postSchoolApply']);

Route::get('passwordreset/{id}/{token}', [Secure\AuthController::class, 'edit'])->name('reminders.edit');
Route::post('passwordreset/{id}/{token}', [Secure\AuthController::class, 'update'])->name('reminders.update');
Route::get('passwordreset', [Secure\AuthController::class, 'reminders']);
Route::post('passwordreset', [Secure\AuthController::class, 'remindersStore']);

Route::get('logout', [Secure\AuthController::class, 'getLogout'])->name('admin.logout');

/*Route::get('about_school_page', [Frontend\PageController::class, 'aboutSchoolPage']);*/
Route::get('about_us', [Frontend\PageController::class, 'aboutSchoolPage']);
Route::get('about_teachers_page', [Frontend\PageController::class, 'aboutTeachersPage']);
Route::get('blogs', [Frontend\BlogController::class, 'index']);
Route::get('faqs', [Frontend\FaqController::class, 'index']);
Route::get('contact', [Frontend\ContactController::class, 'index']);
Route::post('contact', [Frontend\ContactController::class, 'contact']);
Route::get('page/{slug?}', [Frontend\PageController::class, 'show']);
Route::get('blogitem/{slug?}', [Frontend\BlogController::class, 'blog']);

Route::middleware('sentinel', 'xss_protection')->group(function () {
    Route::get('login_as_user/{user}/{company}', [Secure\ProfileController::class, 'loginAsUser']);
    Route::get('back_to_admin', [Secure\ProfileController::class, 'backToAdmin']);
    Route::get('back_to_super_admin', [Secure\ProfileController::class, 'backToSuperAdmin']);

    Route::get('profile', [Secure\ProfileController::class, 'getProfile']);
    Route::get('account', [Secure\ProfileController::class, 'getAccount']);
    Route::post('account', [Secure\ProfileController::class, 'postAccount']);

    Route::get('account', [Secure\ProfileController::class, 'getAccount']);
    Route::post('account', [Secure\ProfileController::class, 'postAccount']);
    Route::get('setyear/{id}', [Secure\ProfileController::class, 'setYear']);
    Route::get('setschool/{id}', [Secure\ProfileController::class, 'setSchool']);
    Route::get('setgroup/{id}', [Secure\ProfileController::class, 'setGroup']);
    Route::get('setstudent/{id}', [Secure\ProfileController::class, 'setStudent']);

    Route::get('mailbox', [Secure\MailboxController::class, 'index']);
    Route::get('mailbox/sent', [Secure\MailboxController::class, 'sent']);
    Route::get('mailbox/compose', [Secure\MailboxController::class, 'compose']);
    Route::post('mailbox/compose', [Secure\MailboxController::class, 'send_compose']);
    Route::get('mailbox/{message}/delete', [Secure\MailboxController::class, 'delete']);
    Route::get('mailbox/{message}/replay', [Secure\MailboxController::class, 'replay']);
    Route::put('mailbox/{message}/replay', [Secure\MailboxController::class, 'send_replay']);
    Route::get('mailbox/{message}/download', [Secure\MailboxController::class, 'download']);

    Route::get('notification/all', [Secure\NotificationController::class, 'getAllData'])->name('admin.ajax_update_notification');
    Route::get('notification/data', [Secure\NotificationController::class, 'data']);
    Route::get('notification/{notification}/show', [Secure\NotificationController::class, 'show']);
    Route::get('notification/{notification}/edit', [Secure\NotificationController::class, 'edit']);
    Route::get('notification/{notification}/delete', [Secure\NotificationController::class, 'delete']);
    Route::resource('notification', Secure\NotificationController::class);


    //manage permissions
    Route::get('permission', [Secure\RoleController::class, 'indexPermission']);
    Route::get('permission/create', [Secure\RoleController::class, 'createPermission']);
    Route::post('permission/store', [Secure\RoleController::class, 'storePermission']);
    Route::get('permission/{permission}/edit', [Secure\RoleController::class, 'editPermission']);
    Route::get('permission/{permission}/show', [Secure\RoleController::class, 'showPermission']);
    Route::post('permission/{permission}/update', [Secure\RoleController::class, 'updatePermission']);
    Route::get('permission/{id}/delete', [Secure\RoleController::class, 'deletePermission']);
    //manage roles
    Route::get('role', [Secure\RoleController::class, 'indexRole']);
    Route::get('role/create', [Secure\RoleController::class, 'createRole']);
    Route::post('role/store', [Secure\RoleController::class, 'storeRole']);
    Route::get('role/{id}/edit', [Secure\RoleController::class, 'editRole']);
    Route::post('role/{id}/update', [Secure\RoleController::class, 'updateRole']);
    Route::get('role/{id}/delete', [Secure\RoleController::class, 'deleteRole']);

    //route for admin users
    Route::middleware('has_any_role:super_admin,admin,employee')->group(function () {
        Route::prefix('login_history')->group(function () {
            Route::get('data', [Secure\LoginHistoryController::class, 'data']);
        });
        Route::resource('login_history', Secure\LoginHistoryController::class);

        Route::get('/bulkEmail', [Secure\StudentAdmissionController::class, 'bulkEmail'])->name('bulkEmail');

        Route::prefix('schoolyear')->group(function () {
            Route::get('data', [Secure\SchoolYearController::class, 'data']);
            Route::put('{schoolYear}', [Secure\SchoolYearController::class, 'update']);
            Route::delete('{schoolYear}', [Secure\SchoolYearController::class, 'destroy']);
            Route::get('{schoolYear}/delete', [Secure\SchoolYearController::class, 'delete']);
            Route::get('{schoolYear}/edit', [Secure\SchoolYearController::class, 'edit']);
            Route::get('{schoolYear}/show', [Secure\SchoolYearController::class, 'show']);
            Route::get('{schoolYear}/{school}/get_sections', [Secure\SchoolYearController::class, 'getSections']);
            Route::get('{section}/get_students', [Secure\SchoolYearController::class, 'getStudents']);
            Route::get('{schoolYear}/copy_data', [Secure\SchoolYearController::class, 'copyData']);
            Route::post('{schoolYear}/post_data', [Secure\SchoolYearController::class, 'postData']);
            Route::get('{schoolYear}/make_alumini', [Secure\SchoolYearController::class, 'makeAlumini']);
            Route::get('{schoolYear}/get_alumini', [Secure\SchoolYearController::class, 'getAlumini']);
            Route::post('{schoolYear}/post_alumini', [Secure\SchoolYearController::class, 'postAlumini']);
            Route::post('{schoolYear}/{alumini}/get_alumini_students', [Secure\SchoolYearController::class, 'getAluminiStudents']);
        });

        Route::prefix('dashboard')->group(function () {
            Route::get('employeeDashboard', [Secure\DashboardController::class, 'employeeDashboard']);
            Route::get('jlcDashboard', [Secure\DashboardController::class, 'jlcDashboard']);
            Route::get('bscDashboard', [Secure\DashboardController::class, 'bscDashboard']);
            Route::get('sectorKpiPlanningDashboard', [Secure\DashboardController::class, 'sectorKpiPlanningDashboard']);
            Route::get('groupKpiPlanningDashboard', [Secure\DashboardController::class, 'groupKpiPlanningDashboard']);
            Route::post('groupEmployeeAging', [Secure\DashboardController::class, 'groupEmployeeAging']);
            Route::post('groupEmployeeAgingPie', [Secure\DashboardController::class, 'groupEmployeeAgingPie']);
            Route::post('groupKpiPerspectiveBalance', [Secure\DashboardController::class, 'groupKpiPerspectiveBalance']);
            Route::post('groupKpiPerspectiveBalancePie', [Secure\DashboardController::class, 'groupKpiPerspectiveBalancePie']);
            Route::post('groupTopObjectives', [Secure\DashboardController::class, 'groupTopObjectives']);
            Route::post('groupTopKras', [Secure\DashboardController::class, 'groupTopKras']);
            Route::get('sectorBscReviewDashboard', [Secure\DashboardController::class, 'sectorBscReviewDashboard']);
            Route::post('sectorBscReviewDashboardFilter', [Secure\DashboardController::class, 'sectorBscReviewDashboardFilter']);
            Route::get('groupBscReviewDashboard', [Secure\DashboardController::class, 'groupBscReviewDashboard']);
            Route::post('groupBscReviewDashboardFilter', [Secure\DashboardController::class, 'groupBscReviewDashboardFilter']);
            Route::get('companyBscReviewDashboard', [Secure\DashboardController::class, 'companyBscReviewDashboard']);
            Route::post('companyBscReviewDashboardFilter', [Secure\DashboardController::class, 'companyBscReviewDashboardFilter']);
            Route::post('companyBscReviewDashboardFilterTable', [Secure\DashboardController::class, 'companyBscReviewDashboardFilterTable']);
            Route::post('companyBscReviewDownload', [Secure\DashboardController::class, 'companyBscReviewDownload']);
            Route::get('leaveDashboard', [Secure\DashboardController::class, 'leaveDashboard']);
            Route::get('groupFinanceDashboard/efficiency', [Secure\DashboardController::class, 'groupFinanceDashboardEfficincy']);
            Route::post('groupFinanceDashboardFilter', [Secure\DashboardController::class, 'groupFinanceDashboardFilter']);
            Route::get('groupFinanceDashboard/monthlyTrend', [Secure\DashboardController::class, 'groupFinanceDashboardMonthlyTrend']);
            Route::post('groupFinanceDashboardMonthlyTrendFilter', [Secure\DashboardController::class, 'groupFinanceDashboardMonthlyTrendFilter']);
            Route::get('sectorFinanceDashboard/efficiency', [Secure\DashboardController::class, 'sectorFinanceDashboardEfficincy']);
            Route::post('sectorFinanceDashboardFilter', [Secure\DashboardController::class, 'sectorFinanceDashboardFilter']);
            Route::get('companyFinanceDashboard/efficiency', [Secure\DashboardController::class, 'companyFinanceDashboardEfficincy']);
            Route::post('companyFinanceDashboardFilter', [Secure\DashboardController::class, 'companyFinanceDashboardFilter']);
            Route::get('procurementDashboard', [Secure\DashboardController::class, 'procurementDashboard']);
            Route::get('fleetDashboard', [Secure\DashboardController::class, 'fleetDashboard']);
            Route::get('payrollDashboard', [Secure\DashboardController::class, 'payrollDashboard']);
            Route::get('attendanceDashboard', [Secure\DashboardController::class, 'attendanceDashboard']);
        });


        Route::prefix('block_login')->group(function () {
            Route::get('data', [Secure\BlockLoginController::class, 'data']);
            Route::delete('{blockLogin}', [Secure\BlockLoginController::class, 'destroy']);
            Route::get('{blockLogin}/delete', [Secure\BlockLoginController::class, 'delete']);
        });
        Route::resource('block_login', Secure\BlockLoginController::class);





        Route::prefix('timetable_period')->group(function () {
            Route::get('data', [Secure\TimetablePeriodController::class, 'data']);
            Route::get('{timetablePeriod}/delete', [Secure\TimetablePeriodController::class, 'delete']);
            Route::get('{timetablePeriod}/show', [Secure\TimetablePeriodController::class, 'show']);
            Route::get('{timetablePeriod}/edit', [Secure\TimetablePeriodController::class, 'edit']);
            Route::put('{timetablePeriod}', [Secure\TimetablePeriodController::class, 'update']);
            Route::get('import', [Secure\TimetablePeriodController::class, 'getImport']);
            Route::post('import', [Secure\TimetablePeriodController::class, 'postImport']);
            Route::post('finish_import', [Secure\TimetablePeriodController::class, 'finishImport']);
            Route::get('download-template', [Secure\TimetablePeriodController::class, 'downloadExcelTemplate']);
        });
        Route::resource('timetable_period', Secure\TimetablePeriodController::class);

        Route::prefix('slider')->group(function () {
            Route::get('data', [Secure\SliderController::class, 'data']);
            Route::get('{slider}/delete', [Secure\SliderController::class, 'delete']);
            Route::get('{slider}/show', [Secure\SliderController::class, 'show']);
            Route::get('{slider}/edit', [Secure\SliderController::class, 'edit']);
            Route::put('{slider}', [Secure\SliderController::class, 'update']);
            Route::get('reorderSlider', [Secure\SliderController::class, 'reorderSlider']);
        });
        Route::resource('slider', Secure\SliderController::class);


        Route::prefix('school_admin')->group(function () {
            Route::get('data', [Secure\SchoolAdminController::class, 'data']);
            Route::get('{school_admin}/edit', [Secure\SchoolAdminController::class, 'edit']);
            Route::get('{school_admin}/delete', [Secure\SchoolAdminController::class, 'delete']);
            Route::get('{school_admin}/show', [Secure\SchoolAdminController::class, 'show']);
        });
        Route::resource('school_admin', Secure\SchoolAdminController::class);

        Route::prefix('school_direction')->group(function () {
            Route::get('data', [Secure\SchoolDirectionController::class, 'data']);
            Route::put('{schoolDirection}', [Secure\SchoolDirectionController::class, 'update']);
            Route::delete('{schoolDirection}', [Secure\SchoolDirectionController::class, 'destroy']);
            Route::get('{schoolDirection}/edit', [Secure\SchoolDirectionController::class, 'edit']);
            Route::get('{schoolDirection}/delete', [Secure\SchoolDirectionController::class, 'delete']);
            Route::get('{schoolDirection}/show', [Secure\SchoolDirectionController::class, 'show']);
        });
        Route::resource('school_direction', Secure\SchoolDirectionController::class);

        Route::get('studentgroup/{section}/create', [Secure\StudentGroupController::class, 'create']);
        Route::get('studentgroup/duration', [Secure\StudentGroupController::class, 'getDuration']);
        Route::put('studentgroup/{studentGroup}', [Secure\StudentGroupController::class, 'update']);
        Route::get('studentgroup/{studentGroup}/generate_csv', [Secure\SectionController::class, 'generateCsvStudentsGroup']);
        Route::get('studentgroup/{section}/{studentGroup}/show', [Secure\StudentGroupController::class, 'show']);
        Route::get('studentgroup/{section}/{studentGroup}/edit', [Secure\StudentGroupController::class, 'edit']);
        Route::get('studentgroup/{section}/{studentGroup}/delete', [Secure\StudentGroupController::class, 'delete']);
        Route::delete('studentgroup/{section}/{studentGroup}', [Secure\StudentGroupController::class, 'destroy']);
        Route::get('studentgroup/{section}/{studentGroup}/students', [Secure\StudentGroupController::class, 'students']);
        Route::put('studentgroup/{section}/{studentGroup}/addstudents', [Secure\StudentGroupController::class, 'addstudents']);
        Route::get('studentgroup/{section}/{studentGroup}/subjects', [Secure\StudentGroupController::class, 'subjects']);
        Route::put('studentgroup/{subject}/{studentGroup}/addeditsubject', [Secure\StudentGroupController::class, 'addeditsubject']);
        Route::get('studentgroup/{section}/{studentGroup}/timetable', [Secure\StudentGroupController::class, 'timetable']);
        Route::get('studentgroup/{section}/{studentGroup}/print_timetable', [Secure\StudentGroupController::class, 'print_timetable']);
        Route::post('studentgroup/{section}/{studentGroup}/addtimetable', [Secure\StudentGroupController::class, 'addtimetable']);
        Route::delete('studentgroup/{section}/{studentGroup}/deletetimetable', [Secure\StudentGroupController::class, 'deletetimetable']);
        Route::resource('studentgroup', Secure\StudentGroupController::class);

        Route::prefix('scholarship')->group(function () {
            Route::get('data', [Secure\ScholarshipController::class, 'data']);
            Route::get('{scholarship}/delete', [Secure\ScholarshipController::class, 'delete']);
            Route::get('{scholarship}/show', [Secure\ScholarshipController::class, 'show']);
            Route::get('{scholarship}/edit', [Secure\ScholarshipController::class, 'edit']);
        });
        Route::resource('scholarship', Secure\ScholarshipController::class);

        Route::prefix('custom_user_field')->group(function () {
            Route::get('data', [Secure\CustomUserFieldController::class, 'data']);
            Route::get('{customUserField}/delete', [Secure\CustomUserFieldController::class, 'delete']);
            Route::get('{customUserField}/show', [Secure\CustomUserFieldController::class, 'show']);
            Route::get('{customUserField}/edit', [Secure\CustomUserFieldController::class, 'edit']);
            Route::delete('{customUserField}', [Secure\CustomUserFieldController::class, 'destroy']);
            Route::put('{customUserField}', [Secure\CustomUserFieldController::class, 'update']);
        });
        Route::resource('custom_user_field', Secure\CustomUserFieldController::class);

        Route::prefix('blog_category')->group(function () {
            Route::get('data', [Secure\BlogCategoryController::class, 'data']);
            Route::get('{blogCategory}/delete', [Secure\BlogCategoryController::class, 'delete']);
            Route::get('{blogCategory}/show', [Secure\BlogCategoryController::class, 'show']);
            Route::get('{blogCategory}/edit', [Secure\BlogCategoryController::class, 'edit']);
            Route::delete('{blogCategory}', [Secure\BlogCategoryController::class, 'destroy']);
            Route::put('{blogCategory}', [Secure\BlogCategoryController::class, 'update']);
        });
        Route::resource('blog_category', Secure\BlogCategoryController::class);

        Route::prefix('blog')->group(function () {
            Route::get('data', [Secure\BlogController::class, 'data']);
            Route::get('{blog}/delete', [Secure\BlogController::class, 'delete']);
            Route::get('{blog}/show', [Secure\BlogController::class, 'show']);
            Route::get('{blog}/edit', [Secure\BlogController::class, 'edit']);
            Route::delete('{blog}', [Secure\BlogController::class, 'destroy']);
            Route::put('{blog}', [Secure\BlogController::class, 'update']);
        });
        Route::resource('blog', Secure\BlogController::class);

        Route::prefix('faq_category')->group(function () {
            Route::get('data', [Secure\FaqCategoryController::class, 'data']);
            Route::get('{faqCategory}/delete', [Secure\FaqCategoryController::class, 'delete']);
            Route::get('{faqCategory}/show', [Secure\FaqCategoryController::class, 'show']);
            Route::get('{faqCategory}/edit', [Secure\FaqCategoryController::class, 'edit']);
            Route::delete('{faqCategory}', [Secure\FaqCategoryController::class, 'destroy']);
            Route::put('{faqCategory}', [Secure\FaqCategoryController::class, 'update']);
        });
        Route::resource('faq_category', Secure\FaqCategoryController::class);

        Route::prefix('faq')->group(function () {
            Route::get('data', [Secure\FaqController::class, 'data']);
            Route::get('{faq}/delete', [Secure\FaqController::class, 'delete']);
            Route::get('{faq}/show', [Secure\FaqController::class, 'show']);
            Route::get('{faq}/edit', [Secure\FaqController::class, 'edit']);
            Route::delete('{faq}', [Secure\FaqController::class, 'destroy']);
            Route::put('{faq}', [Secure\FaqController::class, 'update']);
        });
        Route::resource('faq', Secure\FaqController::class);

        Route::prefix('religion')->group(function () {
            Route::get('data', [Secure\ReligionController::class, 'data']);
            Route::get('{religion}/delete', [Secure\ReligionController::class, 'delete']);
            Route::get('{religion}/show', [Secure\ReligionController::class, 'show']);
            Route::get('{religion}/edit', [Secure\ReligionController::class, 'edit']);
            Route::get('{religion}/user', [Secure\ReligionController::class, 'user']);
        });
        Route::resource('religion', Secure\ReligionController::class);
    });

    Route::middleware('has_any_role:admin,doorman')->group(function () {
        Route::prefix('visitor')->group(function () {
            Route::delete('{visitor}', [Secure\VisitorController::class, 'destroy']);
            Route::get('data', [Secure\VisitorController::class, 'data']);
            Route::get('{visitor}/show', [Secure\VisitorController::class, 'show']);
            Route::get('{visitor}/delete', [Secure\VisitorController::class, 'delete']);
            Route::get('{visitor}/edit', [Secure\VisitorController::class, 'edit']);
        });
        Route::resource('visitor', Secure\VisitorController::class);
    });

    //****************routes for applicant users*******************************

    Route::middleware('has_any_role:applicant,admin,employee')->group(function () {
        Route::resource('applicant_personal', Secure\ApplicantInformationController::class);

        Route::post('applicant/ajaxAddSchool', [Secure\ApplicantSchoolController::class, 'ajaxAddSchool']);

        Route::post('applicant/{applicant}/makeValidate', [Secure\ApplicantController::class, 'makeValidate']);

        Route::post('applicant/DeleteApplicantSchool', [Secure\ApplicantSchoolController::class, 'DeleteApplicantSchool']);

        Route::post('applicant/ajaxAddWaec', [Secure\ApplicantSchoolController::class, 'ajaxAddWaec']);
        Route::post('applicant/DeleteApplicantWaecExam', [Secure\ApplicantSchoolController::class, 'DeleteApplicantWaecExam']);

        Route::post('applicant/ajaxAddWaecSubject', [Secure\ApplicantSchoolController::class, 'ajaxAddWaecSubject']);
        Route::post('applicant/DeleteApplicantWaecSubject', [Secure\ApplicantSchoolController::class, 'DeleteApplicantWaecSubject']);

        Route::post('applicant/ajaxAddNonWaec', [Secure\ApplicantSchoolController::class, 'ajaxAddNonWaec']);
        Route::post('applicant/DeleteApplicantNonWaecExam', [Secure\ApplicantSchoolController::class, 'DeleteApplicantNonWaecExam']);

        Route::prefix('applicant_school')->group(function () {
            Route::delete('{applicant_school}', [Secure\ApplicantSchoolController::class, 'destroy']);
            Route::get('data', [Secure\ApplicantSchoolController::class, 'data']);
            Route::get('{applicant_school}/show', [Secure\ApplicantSchoolController::class, 'show']);
            Route::get('{applicant_school}/delete', [Secure\ApplicantSchoolController::class, 'delete']);
            Route::get('{applicant_school}/edit', [Secure\ApplicantSchoolController::class, 'edit']);
        });
        Route::resource('applicant_school', Secure\ApplicantSchoolController::class);

        Route::prefix('applicant_work')->group(function () {
            Route::delete('{applicant_work}', [Secure\ApplicantWorkController::class, 'destroy']);
            Route::get('data', [Secure\ApplicantWorkController::class, 'data']);
            Route::get('{applicant_work}/show', [Secure\ApplicantWorkController::class, 'show']);
            Route::get('{applicant_work}/delete', [Secure\ApplicantWorkController::class, 'delete']);
            Route::get('{applicant_work}/edit', [Secure\ApplicantWorkController::class, 'edit']);
        });
        Route::resource('applicant_work', Secure\ApplicantWorkController::class);

        Route::prefix('applicant_doc')->group(function () {
            Route::delete('{applicant_doc}', [Secure\ApplicantDocController::class, 'destroy']);
            Route::get('data', [Secure\ApplicantDocController::class, 'data']);
            Route::get('{applicant_doc}/show', [Secure\ApplicantDocController::class, 'show']);
            Route::get('{applicant_doc}/delete', [Secure\ApplicantDocController::class, 'delete']);
            Route::get('{applicant_doc}/edit', [Secure\ApplicantDocController::class, 'edit']);
        });
        Route::resource('applicant_doc', Secure\ApplicantDocController::class);
    });

    Route::middleware('has_any_role:admin,super_admin')->group(function () {
        Route::prefix('all_mails')->group(function () {
            Route::get('/', [Secure\AdminUserMailsController::class, 'index']);
            Route::post('/mails', [Secure\AdminUserMailsController::class, 'getMails']);
        });
    });

    Route::middleware('has_any_role:admin')->group(function () {
        Route::prefix('transportation')->group(function () {
            Route::get('{transportation}/edit', [Secure\TransportationController::class, 'edit']);
            Route::get('{transportation}/delete', [Secure\TransportationController::class, 'delete']);
        });
    });

    Route::middleware('has_any_role:admin,teacher,student,employee,super_admin')->group(function () {
        Route::prefix('transportation')->group(function () {
            Route::get('data', [Secure\TransportationController::class, 'data']);
            Route::get('{transportation}/show', [Secure\TransportationController::class, 'show']);
        });


        Route::get('project/data', [Secure\ProjectController::class, 'data']);
        Route::post('project/data', [Secure\ProjectController::class, 'data']);
        Route::get('project/{project}/show', [Secure\ProjectController::class, 'show']);
        Route::get('project/{project}/approve', [Secure\ProjectController::class, 'approve']);
        Route::get('project/{project}/reject', [Secure\ProjectController::class, 'reject']);
        Route::get('project/{project}/edit', [Secure\ProjectController::class, 'edit']);
        Route::get('project/{project}/delete', [Secure\ProjectController::class, 'delete']);
        Route::resource('project', Secure\ProjectController::class);


        Route::get('crm/client/data', [Secure\ClientController::class, 'data']);
        Route::post('crm/client/data', [Secure\ClientController::class, 'data']);
        Route::get('crm/client/{client}/show', [Secure\ClientController::class, 'show']);
        Route::get('crm/client/{client}/edit', [Secure\ClientController::class, 'edit']);
        Route::get('crm/client/{client}/delete', [Secure\ClientController::class, 'delete']);
        Route::resource('crm/client', Secure\ClientController::class);
        Route::post('ajax/findRegionDistricts', [Secure\ClientController::class, 'findRegionDistricts']);



        Route::get('crm/client_complaint/data', [Secure\ProjectController::class, 'data']);
        Route::post('crm/client_complaint/data', [Secure\ProjectController::class, 'data']);
        Route::get('crm/client_complaint/{client_complaint}/show', [Secure\ProjectController::class, 'show']);
        Route::get('crm/client_complaint/{client_complaint}/edit', [Secure\ProjectController::class, 'edit']);
        Route::get('crm/client_complaint/{client_complaint}/delete', [Secure\ProjectController::class, 'delete']);
        Route::resource('crm/client_complaint', Secure\ProjectController::class);

        Route::get('crm/client_category/data', [Secure\ClientCategoryController::class, 'data']);
        Route::post('crm/client_category/data', [Secure\ClientCategoryController::class, 'data']);
        Route::get('crm/client_category/{client_category}/show', [Secure\ClientCategoryController::class, 'show']);
        Route::get('crm/client_category/{client_category}/edit', [Secure\ClientCategoryController::class, 'edit']);
        Route::get('crm/client_category/{client_category}/delete', [Secure\ClientCategoryController::class, 'delete']);
        Route::resource('crm/client_category', Secure\ClientCategoryController::class);



        Route::get('crm/client_request/data', [Secure\ProjectController::class, 'data']);
        Route::post('crm/client_request/data', [Secure\ProjectController::class, 'data']);
        Route::get('crm/client_request/{client_request}/show', [Secure\ProjectController::class, 'show']);
        Route::get('crm/client_request/{client_request}/edit', [Secure\ProjectController::class, 'edit']);
        Route::get('crm/client_request/{client_request}/delete', [Secure\ProjectController::class, 'delete']);
        Route::resource('crm/client_request', Secure\ProjectController::class);



        Route::get('crm/service_request/data', [Secure\ProjectController::class, 'data']);
        Route::post('crm/service_request/data', [Secure\ProjectController::class, 'data']);
        Route::get('crm/service_request/{service_request}/show', [Secure\ProjectController::class, 'show']);
        Route::get('crm/service_request/{service_request}/edit', [Secure\ProjectController::class, 'edit']);
        Route::get('crm/service_request/{service_request}/delete', [Secure\ProjectController::class, 'delete']);
        Route::resource('crm/service_request', Secure\ProjectController::class);


        Route::get('projectCategory/data', [Secure\ProjectCategoryController::class, 'data']);
        Route::get('projectCategory/{projectCategory}/show', [Secure\ProjectCategoryController::class, 'show']);
        Route::get('projectCategory/{projectCategory}/edit', [Secure\ProjectCategoryController::class, 'edit']);
        Route::get('projectCategory/{projectCategory}/delete', [Secure\ProjectCategoryController::class, 'delete']);
        Route::resource('projectCategory', Secure\ProjectCategoryController::class);



        Route::get('projectArtisan/data', [Secure\ProjectArtisanController::class, 'data']);
        Route::get('projectArtisan/{projectArtisan}/show', [Secure\ProjectArtisanController::class, 'show']);
        Route::get('projectArtisan/{projectArtisan}/edit', [Secure\ProjectArtisanController::class, 'edit']);
        Route::get('projectArtisan/{projectArtisan}/delete', [Secure\ProjectArtisanController::class, 'delete']);
        Route::resource('projectArtisan', Secure\ProjectArtisanController::class);



        Route::get('test/data', [Secure\TestController::class, 'data']);
        Route::post('test/data', [Secure\TestController::class, 'data']);
        Route::get('test/{test}/show', [Secure\TestController::class, 'show']);
        Route::get('test/{test}/edit', [Secure\TestController::class, 'edit']);
        Route::get('test/{test}/delete', [Secure\TestController::class, 'delete']);
        Route::resource('test', Secure\TestController::class);
    });

    Route::middleware('has_any_role:doorman')->group(function () {
        Route::prefix('visitor_visit')->group(function () {
            Route::get('data', [Secure\VisitorVisitController::class, 'data']);
            Route::get('{visitorLog}/edit', [Secure\VisitorVisitController::class, 'edit']);
            Route::get('{visitorLog}/show', [Secure\VisitorVisitController::class, 'show']);
        });
        Route::resource('visitor_visit', Secure\VisitorVisitController::class);
    });



    Route::get('teacher_subject/data', [Secure\TeacherSubjectController::class, 'data']);
    Route::get('teacher_subject/{teacher_subject}/show',  [Secure\TeacherSubjectController::class, 'show']);
    Route::get('teacher_subject/{teacher_subject}/edit',  [Secure\TeacherSubjectController::class, 'edit']);
    Route::get('teacher_subject/{teacher_subject}/delete',  [Secure\TeacherSubjectController::class, 'delete']);
    Route::resource('teacher_subject', Secure\TeacherSubjectController::class);


    Route::get('course_category/data', [Secure\CourseCategoryController::class, 'data']);
    Route::get('course_category/{courseCategory}/show', [Secure\CourseCategoryController::class, 'show']);
    Route::get('course_category/{courseCategory}/edit',  [Secure\CourseCategoryController::class, 'edit']);
    Route::get('course_category/{courseCategory}/delete',  [Secure\CourseCategoryController::class, 'delete']);
    Route::resource('course_category', Secure\CourseCategoryController::class);

    Route::middleware('has_any_role:admin,employee')->group(function () {
        Route::get('student_attendances_admin', [Secure\StudentAttendanceAdminController::class, 'index']);
        Route::post('student_attendances_admin/attendance', [Secure\StudentAttendanceAdminController::class, 'attendance']);
        Route::post('student_attendances_admin/attendanceAjax', [Secure\StudentAttendanceAdminController::class, 'attendanceAjax']);

        Route::get('course_attendance', [Secure\CourseAttendanceController::class, 'index']);
        Route::post('course_attendance/attendance', [Secure\CourseAttendanceController::class, 'attendance']);
        Route::post('course_attendance/attendanceAjax', [Secure\CourseAttendanceController::class, 'attendanceAjax']);

        Route::get('attendances_by_subject', [Secure\StudentAttendanceAdminBySubjectController::class, 'index']);
        Route::post('attendances_by_subject/get_groups', [Secure\StudentAttendanceAdminBySubjectController::class, 'getGroups']);
        Route::post('attendances_by_subject/get_students', [Secure\StudentAttendanceAdminBySubjectController::class, 'getStudents']);
        Route::post('attendances_by_subject/attendance_graph', [Secure\StudentAttendanceAdminBySubjectController::class, 'attendanceGraph']);

        Route::get('admin_exam', [Secure\AdminExamController::class, 'index']);
        Route::get('admin_exam/create_by_group', [Secure\AdminExamController::class, 'create_by_group']);
        Route::get('admin_exam/create_by_subject', [Secure\AdminExamController::class, 'create_by_subject']);
        Route::get('admin_exam/{studentGroup}/subjects', [Secure\AdminExamController::class, 'subjects']);
        Route::post('admin_exam/store_by_group', [Secure\AdminExamController::class, 'store_by_group']);
        Route::post('admin_exam/store_by_subject', [Secure\AdminExamController::class, 'store_by_subject']);
        Route::get('admin_exam/data', [Secure\AdminExamController::class, 'data']);
        Route::get('admin_exam/{exam}/show', [Secure\AdminExamController::class, 'show']);
    });

    Route::middleware('has_any_role:admin,super_admin,librarian,employee')->group(function () {
        Route::get('task', [Secure\TaskController::class, 'index']);
        Route::post('task/create', [Secure\TaskController::class, 'store']);
        Route::get('task/data', [Secure\TaskController::class, 'data']);
        Route::post('task/{task}/edit', [Secure\TaskController::class, 'update']);
        Route::post('task/{task}/delete', [Secure\TaskController::class, 'delete']);
    });
    Route::middleware('has_any_role:accountant,librarian')->group(function () {
        Route::get('return_book_penalty', [Secure\ReturnBookPenaltyController::class, 'index']);
        Route::get('return_book_penalty/data', [Secure\ReturnBookPenaltyController::class, 'data']);
    });

    Route::middleware('has_any_role:human_resources,admin,super_admin,accountant,employee')->group(function () {
        Route::prefix('teacher')->group(function () {
            Route::get('data', [Secure\TeacherController::class, 'data']);
            Route::get('import', [Secure\TeacherController::class, 'getImport']);
            Route::post('import', [Secure\TeacherController::class, 'postImport']);
            Route::post('finish_import', [Secure\TeacherController::class, 'finishImport']);
            Route::get('download-template', [Secure\TeacherController::class, 'downloadExcelTemplate']);
            Route::get('export', [Secure\TeacherController::class, 'export']);
            Route::get('get_exists', [Secure\TeacherController::class, 'getExists']);
            Route::delete('{teacher_user}', [Secure\TeacherController::class, 'destroy']);
            Route::put('{teacher_user}', [Secure\TeacherController::class, 'update']);
            Route::get('{teacher_user}/show', [Secure\TeacherController::class, 'show']);
            Route::get('{teacher_user}/edit', [Secure\TeacherController::class, 'edit']);
            Route::get('{teacher_user}/delete', [Secure\TeacherController::class, 'delete']);
        });
        Route::resource('teacher', Secure\TeacherController::class);

        Route::get('fee_category/data', [Secure\FeeCategoryController::class, 'data']);
        Route::put('fee_category/{feeCategory}', [Secure\FeeCategoryController::class, 'update']);
        Route::delete('fee_category/{feeCategory}', [Secure\FeeCategoryController::class, 'destroy']);
        Route::get('fee_category/{feeCategory}/show', [Secure\FeeCategoryController::class, 'show']);
        Route::get('fee_category/{feeCategory}/edit', [Secure\FeeCategoryController::class, 'edit']);
        Route::get('fee_category/{feeCategory}/delete', [Secure\FeeCategoryController::class, 'delete']);
        Route::resource('fee_category', Secure\FeeCategoryController::class);

        Route::prefix('join_date/{teacher_user}')->group(function () {
            Route::get('', [Secure\JoinDateController::class, 'index']);
            Route::post('', [Secure\JoinDateController::class, 'store']);
            Route::get('create', [Secure\JoinDateController::class, 'create']);
            Route::get('data', [Secure\JoinDateController::class, 'data']);
            Route::delete('{joinDate}', [Secure\JoinDateController::class, 'destroy']);
            Route::put('{joinDate}', [Secure\JoinDateController::class, 'update']);
            Route::get('{joinDate}/show', [Secure\JoinDateController::class, 'show']);
            Route::get('{joinDate}/edit', [Secure\JoinDateController::class, 'edit']);
            Route::get('{joinDate}/delete', [Secure\JoinDateController::class, 'delete']);
        });

        Route::prefix('staff_salary/{teacher_user}')->group(function () {
            Route::get('', [Secure\StaffSalaryController::class, 'index']);
            Route::post('', [Secure\StaffSalaryController::class, 'store']);
            Route::get('create', [Secure\StaffSalaryController::class, 'create']);
            Route::get('data', [Secure\StaffSalaryController::class, 'data']);
            Route::delete('{staffSalary}', [Secure\StaffSalaryController::class, 'destroy']);
            Route::put('{staffSalary}', [Secure\StaffSalaryController::class, 'update']);
            Route::get('{staffSalary}/show', [Secure\StaffSalaryController::class, 'show']);
            Route::get('{staffSalary}/edit', [Secure\StaffSalaryController::class, 'edit']);
            Route::get('{staffSalary}/delete', [Secure\StaffSalaryController::class, 'delete']);
        });

        Route::get('librarian/data', [Secure\LibrarianController::class, 'data']);
        Route::get('librarian/{librarian_user}/show', [Secure\LibrarianController::class, 'show']);
        Route::get('librarian/{librarian_user}/edit', [Secure\LibrarianController::class, 'edit']);
        Route::get('librarian/{librarian_user}/delete', [Secure\LibrarianController::class, 'delete']);
        Route::resource('librarian', Secure\LibrarianController::class);

        Route::get('doorman/data', [Secure\DoormanController::class, 'data']);
        Route::get('doorman/{doorman_user}/show', [Secure\DoormanController::class, 'show']);
        Route::get('doorman/{doorman_user}/edit', [Secure\DoormanController::class, 'edit']);
        Route::get('doorman/{doorman_user}/delete', [Secure\DoormanController::class, 'delete']);
        Route::resource('doorman', Secure\DoormanController::class);

        Route::prefix('country')->group(function () {
            Route::get('data', [Secure\CountryController::class, 'data']);
            Route::get('{country}/delete', [Secure\CountryController::class, 'delete']);
            Route::get('{country}/show', [Secure\CountryController::class, 'show']);
            Route::get('{country}/edit', [Secure\CountryController::class, 'edit']);
            Route::put('{country}', [Secure\CountryController::class, 'update']);
        });
        Route::resource('country', Secure\CountryController::class);

        Route::prefix('marital_status')->group(function () {
            Route::get('data', [Secure\MaritalStatusController::class, 'data']);
            Route::get('{maritalStatus}/delete', [Secure\MaritalStatusController::class, 'delete']);
            Route::get('{maritalStatus}/show', [Secure\MaritalStatusController::class, 'show']);
            Route::get('{maritalStatus}/edit', [Secure\MaritalStatusController::class, 'edit']);
            Route::get('{maritalStatus}/user', [Secure\MaritalStatusController::class, 'user']);
        });
        Route::resource('marital_status', Secure\MaritalStatusController::class);

        Route::prefix('qualification')->group(function () {
            Route::get('data', [Secure\QualificationController::class, 'data']);
            Route::get('{qualification}/delete', [Secure\QualificationController::class, 'delete']);
            Route::get('{qualification}/show', [Secure\QualificationController::class, 'show']);
            Route::get('{qualification}/edit', [Secure\QualificationController::class, 'edit']);
            Route::post('addEmployees', [Secure\QualificationController::class, 'addEmployees']);
            Route::get('{qualification}/user', [Secure\QualificationController::class, 'user']);
        });
        Route::resource('qualification', Secure\QualificationController::class);

        Route::get('levels/data', [Secure\LevelController::class, 'data']);
        Route::get('levels/{level}/show', [Secure\LevelController::class, 'show']);
        Route::get('levels/{level}/edit', [Secure\LevelController::class, 'edit']);
        Route::get('levels/{level}/delete', [Secure\LevelController::class, 'delete']);
        Route::resource('levels', Secure\LevelController::class);

        Route::get('helpDesk/me', [Secure\HelpDeskController::class, 'me']);
        Route::get('helpDesk/mine', [Secure\HelpDeskController::class, 'mine']);
        Route::get('helpDesk/closed', [Secure\HelpDeskController::class, 'closed']);
        Route::get('helpDesk/open', [Secure\HelpDeskController::class, 'open']);
        Route::get('helpDesk/{helpDesk}/show', [Secure\HelpDeskController::class, 'show']);
        Route::get('helpDesk/{helpDesk}/edit', [Secure\HelpDeskController::class, 'edit']);
        Route::get('helpDesk/{helpDesk}/delete', [Secure\HelpDeskController::class, 'delete']);
        Route::get('helpDesk/createFor', [Secure\HelpDeskController::class, 'createFor']);
        Route::resource('helpDesk', Secure\HelpDeskController::class);

        Route::get('dailyActivity/indexAll', [Secure\DailyActivityController::class, 'indexAll']);
        Route::post('dailyActivity/filter', [Secure\DailyActivityController::class, 'filter']);
        Route::get('dailyActivity/{dailyActivity}/show', [Secure\DailyActivityController::class, 'show']);
        Route::get('dailyActivity/{dailyActivity}/edit', [Secure\DailyActivityController::class, 'edit']);
        Route::get('dailyActivity/{dailyActivity}/delete', [Secure\DailyActivityController::class, 'delete']);
        Route::resource('dailyActivity', Secure\DailyActivityController::class);

        Route::get('hrPolicy/{hrPolicy}/show', [Secure\HrPolicyController::class, 'show']);
        Route::get('hrPolicy/{hrPolicy}/edit', [Secure\HrPolicyController::class, 'edit']);
        Route::get('hrPolicy/{hrPolicy}/delete', [Secure\HrPolicyController::class, 'delete']);
        Route::resource('hrPolicy', Secure\HrPolicyController::class);

        Route::get('employeeShift/{employeeShift}/show', [Secure\EmployeeShiftController::class, 'show']);
        Route::get('employeeShift/{employeeShift}/edit', [Secure\EmployeeShiftController::class, 'edit']);
        Route::get('employeeShift/{employeeShift}/delete', [Secure\EmployeeShiftController::class, 'delete']);
        Route::resource('employeeShift', Secure\EmployeeShiftController::class);

        Route::get('visitorLog/indexAll', [Secure\VisitorLogController::class, 'indexAll']);
        Route::post('visitorLog/filter', [Secure\VisitorLogController::class, 'filter']);
        Route::get('visitorLog/{visitorLog}/show', [Secure\VisitorLogController::class, 'show']);
        Route::get('visitorLog/{visitorLog}/edit', [Secure\VisitorLogController::class, 'edit']);
        Route::get('visitorLog/{visitorLog}/delete', [Secure\VisitorLogController::class, 'delete']);
        Route::resource('visitorLog', Secure\VisitorLogController::class);

        Route::get('procurementRequest/indexAll', [Secure\ProcurementRequestController::class, 'indexAll']);
        Route::post('procurementRequest/filter', [Secure\ProcurementRequestController::class, 'filter']);
        Route::get('procurementRequest/{procurementRequest}/show', [Secure\ProcurementRequestController::class, 'show']);
        Route::get('procurementRequest/{procurementRequest}/edit', [Secure\ProcurementRequestController::class, 'edit']);
        Route::get('procurementRequest/{procurementRequest}/delete', [Secure\ProcurementRequestController::class, 'delete']);
        Route::resource('procurementRequest', Secure\ProcurementRequestController::class);

        Route::get('article/data', [Secure\ArticleController::class, 'data']);
        Route::post('article/addComment', [Secure\ArticleController::class, 'addComment']);
        Route::get('article/{article}/show', [Secure\ArticleController::class, 'show']);
        Route::get('article/{article}/edit', [Secure\ArticleController::class, 'edit']);
        Route::get('article/{article}/delete', [Secure\ArticleController::class, 'delete']);
        Route::resource('article', Secure\ArticleController::class);


        Route::get('publication/data', [Secure\PublicationController::class, 'data']);
        Route::post('publication/addComment', [Secure\PublicationController::class, 'addComment']);
        Route::get('publication/{publication}/show', [Secure\PublicationController::class, 'show']);
        Route::get('publication/{publication}/edit', [Secure\PublicationController::class, 'edit']);
        Route::get('publication/{publication}/delete', [Secure\PublicationController::class, 'delete']);
        Route::resource('publication', Secure\PublicationController::class);


        Route::get('publication_category/data', [Secure\PublicationController::class, 'data']);
        Route::get('publication_category/{publication_category}/show', [Secure\PublicationCategoryController::class, 'show']);
        Route::get('publication_category/{publication_category}/publications', [Secure\PublicationCategoryController::class, 'publications']);
        Route::get('publication_category/{publication_category}/edit', [Secure\PublicationCategoryController::class, 'edit']);
        Route::get('publication_category/{publication_category}/delete', [Secure\PublicationCategoryController::class, 'delete']);
        Route::resource('publication_category', Secure\PublicationCategoryController::class);

        Route::get('post/data', [Secure\PostController::class, 'data']);
        Route::post('post/addComment', [Secure\PostController::class, 'addComment']);
        Route::get('post/{post}/show', [Secure\PostController::class, 'show']);
        Route::get('post/{post}/edit', [Secure\PostController::class, 'edit']);
        Route::get('post/{post}/delete', [Secure\PostController::class, 'delete']);
        Route::post('post/likePost', [Secure\PostController::class, 'likePost']);
        Route::resource('post', Secure\PostController::class);


        Route::get('safetyTip/data', [Secure\SafetyTipController::class, 'data']);
        Route::post('safetyTip/addComment', [Secure\SafetyTipController::class, 'addComment']);
        Route::get('safetyTip/{safetyTip}/show', [Secure\SafetyTipController::class, 'show']);
        Route::get('safetyTip/{safetyTip}/edit', [Secure\SafetyTipController::class, 'edit']);
        Route::get('safetyTip/{safetyTip}/delete', [Secure\SafetyTipController::class, 'delete']);
        Route::resource('safetyTip', Secure\SafetyTipController::class);

        Route::get('position/data', [Secure\PositionController::class, 'data']);
        Route::get('position/{position}/show', [Secure\PositionController::class, 'show']);
        Route::get('position/{position}/edit', [Secure\PositionController::class, 'edit']);
        Route::get('position/{position}/delete', [Secure\PositionController::class, 'delete']);
        Route::post('position/addCompetency', [Secure\PositionController::class, 'addCompetency']);
        Route::get('position/erpSync', [Secure\PositionController::class, 'erpSync']);
        Route::resource('position', Secure\PositionController::class);

        Route::get('procurementItem/data', [Secure\ProcurementItemController::class, 'data']);
        Route::get('procurementItem/planIndex', [Secure\ProcurementItemController::class, 'planIndex']);
        Route::get('procurementItem/{company}/planShow', [Secure\ProcurementItemController::class, 'planShow']);
        Route::post('procurementItem/addItemSuppliers', [Secure\ProcurementItemController::class, 'addItemSuppliers']);
        Route::post('procurementItem/addItemPlan', [Secure\ProcurementItemController::class, 'addItemPlan']);
        Route::get('procurementItem/{procurementItem}/show', [Secure\ProcurementItemController::class, 'show']);
        Route::get('procurementItem/{procurementItem}/edit', [Secure\ProcurementItemController::class, 'edit']);
        Route::get('procurementItem/{procurementItem}/delete', [Secure\ProcurementItemController::class, 'delete']);
        Route::get('procurementItem/{procurementPlan}/deletePlan', [Secure\ProcurementItemController::class, 'deletePlan']);
        Route::get('procurementItemSupplier/{procurementItemSupplier}/delete', [Secure\ProcurementItemController::class, 'deleteItemSupplier']);
        Route::post('procurementItem/generalExport', [Secure\ProcurementItemController::class, 'generalExport']);
        Route::get('procurementItem/import', [Secure\ProcurementItemController::class, 'getImport'])->name('procurementItem.import');
        Route::post('procurementItem/import', [Secure\ProcurementItemController::class, 'postImport']);
        Route::post('procurementItem/finish_import', [Secure\ProcurementItemController::class, 'finishImport']);
        Route::get('procurementItem/download-template', [Secure\ProcurementItemController::class, 'downloadExcelTemplate']);
        Route::get('procurementItem/export', [Secure\ProcurementItemController::class, 'export']);
        Route::resource('procurementItem', Secure\ProcurementItemController::class);

        Route::get('procurementCategory/data', [Secure\ProcurementCategoryController::class, 'data']);
        Route::get('procurementCategory/{procurementCategory}/show', [Secure\ProcurementCategoryController::class, 'show']);
        Route::get('procurementCategory/{procurementCategory}/edit', [Secure\ProcurementCategoryController::class, 'edit']);
        Route::get('procurementCategory/{procurementCategory}/delete', [Secure\ProcurementCategoryController::class, 'delete']);
        Route::resource('procurementCategory', Secure\ProcurementCategoryController::class);

        Route::get('supplier/data', [Secure\SupplierController::class, 'data']);
        Route::get('supplier/contract', [Secure\SupplierController::class, 'contract']);
        Route::post('supplier/addSupplierItems', [Secure\SupplierController::class, 'addSupplierItems']);
        Route::post('supplier/addSupplierDocuments', [Secure\SupplierController::class, 'addSupplierDocuments']);
        Route::get('supplier/{supplier}/show', [Secure\SupplierController::class, 'show']);
        Route::get('supplier/{supplier}/showContract', [Secure\SupplierController::class, 'showContract']);
        Route::get('supplier/{supplier}/edit', [Secure\SupplierController::class, 'edit']);
        Route::get('supplier/{supplier}/delete', [Secure\SupplierController::class, 'delete']);
        Route::get('supplier/{procurementItemSupplier}/deleteSupplierItem', [Secure\SupplierController::class, 'deleteSupplierItem']);
        Route::get('supplier/{supplierDocument}/deleteSupplierDocument', [Secure\SupplierController::class, 'deleteSupplierDocument']);
        Route::resource('supplier', Secure\SupplierController::class);



        Route::get('fleetType/data', [Secure\FleetTypeController::class, 'data']);
        Route::get('fleetType/{fleetType}/show', [Secure\FleetTypeController::class, 'show']);
        Route::get('fleetType/{fleetType}/edit', [Secure\FleetTypeController::class, 'edit']);
        Route::get('fleetType/{fleetType}/delete', [Secure\FleetTypeController::class, 'delete']);
        Route::resource('fleetType', Secure\FleetTypeController::class);

        Route::get('fleetCategory/data', [Secure\FleetCategoryController::class, 'data']);
        Route::get('fleetCategory/{fleetCategory}/show', [Secure\FleetCategoryController::class, 'show']);
        Route::get('fleetCategory/{fleetCategory}/edit', [Secure\FleetCategoryController::class, 'edit']);
        Route::get('fleetCategory/{fleetCategory}/delete', [Secure\FleetCategoryController::class, 'delete']);
        Route::resource('fleetCategory', Secure\FleetCategoryController::class);


        Route::get('fleet/data', [Secure\FleetController::class, 'data']);
        Route::get('fleet/{fleet}/show', [Secure\FleetController::class, 'show']);
        Route::get('fleet/{fleet}/edit', [Secure\FleetController::class, 'edit']);
        Route::get('fleet/{fleet}/delete', [Secure\FleetController::class, 'delete']);
        Route::resource('fleet', Secure\FleetController::class);



        Route::get('fleetOperation/data', [Secure\FleetOperationsController::class, 'data']);
        Route::get('fleetOperation/{fleetOperation}/show', [Secure\FleetOperationsController::class, 'show']);
        Route::get('fleetOperation/{fleetOperation}/edit', [Secure\FleetOperationsController::class, 'edit']);
        Route::get('fleetOperation/{fleetOperation}/delete', [Secure\FleetOperationsController::class, 'delete']);
        Route::resource('fleetOperation', Secure\FleetOperationsController::class);


        Route::get('driver/data', [Secure\FleetCategoryController::class, 'data']);
        Route::get('driver/{driver}/show', [Secure\FleetCategoryController::class, 'show']);
        Route::get('driver/{driver}/edit', [Secure\FleetCategoryController::class, 'edit']);
        Route::get('driver/{driver}/delete', [Secure\FleetCategoryController::class, 'delete']);
        Route::resource('driver', Secure\FleetCategoryController::class);



        Route::get('fleetMaintenance/data', [Secure\FleetCategoryController::class, 'data']);
        Route::get('fleetMaintenance/{fleetMaintenance}/show', [Secure\FleetCategoryController::class, 'show']);
        Route::get('fleetMaintenance/{fleetMaintenance}/edit', [Secure\FleetCategoryController::class, 'edit']);
        Route::get('fleetMaintenance/{fleetMaintenance}/delete', [Secure\FleetCategoryController::class, 'delete']);
        Route::resource('fleetMaintenance', Secure\FleetCategoryController::class);



        Route::get('accident/data', [Secure\FleetCategoryController::class, 'data']);
        Route::get('accident/{accident}/show', [Secure\FleetCategoryController::class, 'show']);
        Route::get('accident/{accident}/edit', [Secure\FleetCategoryController::class, 'edit']);
        Route::get('accident/{accident}/delete', [Secure\FleetCategoryController::class, 'delete']);
        Route::resource('accident', Secure\FleetCategoryController::class);




        Route::get('legalCaseCategory/data', [Secure\LegalCaseCategoryController::class, 'data']);
        Route::get('legalCaseCategory/{legalCaseCategory}/show', [Secure\LegalCaseCategoryController::class, 'show']);
        Route::get('legalCaseCategory/{legalCaseCategory}/edit', [Secure\LegalCaseCategoryController::class, 'edit']);
        Route::get('legalCaseCategory/{legalCaseCategory}/delete', [Secure\LegalCaseCategoryController::class, 'delete']);
        Route::resource('legalCaseCategory', Secure\LegalCaseCategoryController::class);

        Route::get('legalCase/data', [Secure\LegalCaseController::class, 'data']);
        Route::get('legalCase/{legalCase}/show', [Secure\LegalCaseController::class, 'show']);
        Route::get('legalCase/{legalCase}/edit', [Secure\LegalCaseController::class, 'edit']);
        Route::get('legalCase/{legalCase}/delete', [Secure\LegalCaseController::class, 'delete']);
        Route::get('legalCase/comments/{legalCase}', [Secure\LegalCaseController::class, 'latestCaseUpdates']);
        Route::post('legalCase/addComment', [Secure\LegalCaseController::class, 'addComment']);
        Route::resource('legalCase', Secure\LegalCaseController::class);

        Route::get('legalFirm/data', [Secure\LegalFirmController::class, 'data']);
        Route::get('legalFirm/{legalFirm}/show', [Secure\LegalFirmController::class, 'show']);
        Route::get('legalFirm/{legalFirm}/edit', [Secure\LegalFirmController::class, 'edit']);
        Route::get('legalFirm/{legalFirm}/delete', [Secure\LegalFirmController::class, 'delete']);
        Route::resource('legalFirm', Secure\LegalFirmController::class);

        Route::get('legalRequest/indexAll', [Secure\LegalRequestController::class, 'indexAll']);
        Route::post('legalRequest/filter', [Secure\LegalRequestController::class, 'filter']);
        Route::get('legalRequest/{legalRequest}/show', [Secure\LegalRequestController::class, 'show']);
        Route::get('legalRequest/{legalRequest}/edit', [Secure\LegalRequestController::class, 'edit']);
        Route::get('legalRequest/{legalRequest}/delete', [Secure\LegalRequestController::class, 'delete']);
        Route::post('legalRequest/addComment', [Secure\LegalRequestController::class, 'addComment']);
        Route::resource('legalRequest', Secure\LegalRequestController::class);


        Route::get('employeeRequest/indexAll', [Secure\EmployeeRequestController::class, 'indexAll']);
        Route::post('employeeRequest/filter', [Secure\EmployeeRequestController::class, 'filter']);
        Route::get('employeeRequest/{employeeRequest}/show', [Secure\EmployeeRequestController::class, 'show']);
        Route::get('employeeRequest/{employeeRequest}/approve', [Secure\EmployeeRequestController::class, 'approve']);
        Route::get('employeeRequest/{employeeRequest}/modalShowApprove', [Secure\EmployeeRequestController::class, 'modalShowApprove']);
        Route::get('employeeRequest/{employeeRequest}/edit', [Secure\EmployeeRequestController::class, 'edit']);
        Route::get('employeeRequest/{employeeRequest}/delete', [Secure\EmployeeRequestController::class, 'delete']);
        Route::post('employeeRequest/addComment', [Secure\EmployeeRequestController::class, 'addComment']);
        Route::get('employeeRequest/approvals', [Secure\EmployeeRequestController::class, 'requestApprovals']);
        Route::get('employeeRequest/copy', [Secure\EmployeeRequestController::class, 'requestCopy']);
        Route::resource('employeeRequest', Secure\EmployeeRequestController::class);




        Route::get('employeeIdea/indexAll', [Secure\EmployeeRequestController::class, 'indexAll']);
        Route::post('employeeIdea/filter', [Secure\EmployeeRequestController::class, 'filter']);
        Route::get('employeeIdea/{employeeIdea}/show', [Secure\EmployeeRequestController::class, 'show']);
        Route::get('employeeIdea/{employeeIdea}/approve', [Secure\EmployeeRequestController::class, 'approve']);
        Route::get('employeeIdea/{employeeIdea}/modalShowApprove', [Secure\EmployeeRequestController::class, 'modalShowApprove']);
        Route::get('employeeIdea/{employeeIdea}/edit', [Secure\EmployeeRequestController::class, 'edit']);
        Route::get('employeeIdea/{employeeIdea}/delete', [Secure\EmployeeRequestController::class, 'delete']);
        Route::post('employeeIdea/addComment', [Secure\EmployeeRequestController::class, 'addComment']);
        Route::get('employeeIdea/approvals', [Secure\EmployeeRequestController::class, 'requestApprovals']);
        Route::get('employeeIdea/{employeeIdeaCampaign}/createIdea', [Secure\EmployeeIdeaController::class, 'createForCampaign']);
        Route::resource('employeeIdea', Secure\EmployeeIdeaController::class);



        Route::get('employeeIdeaCampaign/{employeeIdeaCampaign}/show', [Secure\EmployeeIdeaCampaignController::class, 'show']);
        Route::get('employeeIdeaCampaign/{employeeIdeaCampaign}/modalShowApprove', [Secure\EmployeeIdeaCampaignController::class, 'modalShowApprove']);
        Route::get('employeeIdeaCampaign/{employeeIdeaCampaign}/edit', [Secure\EmployeeIdeaCampaignController::class, 'edit']);
        Route::get('employeeIdeaCampaign/{employeeIdeaCampaign}/delete', [Secure\EmployeeIdeaCampaignController::class, 'delete']);
        Route::post('employeeIdeaCampaign/addComment', [Secure\EmployeeIdeaCampaignController::class, 'addComment']);
        Route::get('employeeIdeaCampaign/approvals', [Secure\EmployeeIdeaCampaignController::class, 'requestApprovals']);
        Route::resource('employeeIdeaCampaign', Secure\EmployeeIdeaCampaignController::class);


        Route::get('competency/data', [Secure\CompetencyController::class, 'data']);
        Route::get('competency/{competency}/show', [Secure\CompetencyController::class, 'show']);
        Route::get('competency/{competency}/edit', [Secure\CompetencyController::class, 'edit']);
        Route::get('competency/{competency}/delete', [Secure\CompetencyController::class, 'delete']);
        Route::post('competency/addCompetencyLevel', [Secure\CompetencyController::class, 'addCompetencyLevel']);
        Route::resource('competency', Secure\CompetencyController::class);

        Route::get('competency_level/data', [Secure\CompetencyLevelController::class, 'data']);
        Route::get('competency_level/{competency_level}/show', [Secure\CompetencyLevelController::class, 'show']);
        Route::get('competency_level/{competency_level}/edit', [Secure\CompetencyLevelController::class, 'edit']);
        Route::get('competency_level/{competency_level}/delete', [Secure\CompetencyLevelController::class, 'delete']);
        Route::post('competency_level/addEmployees', [Secure\CompetencyLevelController::class, 'addEmployees']);
        Route::resource('competency_level', Secure\CompetencyLevelController::class);

        Route::get('competency_grade/data', [Secure\CompetencyLevelController::class, 'data']);
        Route::get('competency_grade/{competency_grade}/show', [Secure\CompetencyGradeController::class, 'show']);
        Route::get('competency_grade/{competency_grade}/edit', [Secure\CompetencyGradeController::class, 'edit']);
        Route::get('competency_grade/{competency_grade}/delete', [Secure\CompetencyGradeController::class, 'delete']);
        Route::post('competency_grade/addEmployees', [Secure\CompetencyGradeController::class, 'addEmployees']);
        Route::resource('competency_grade', Secure\CompetencyGradeController::class);

        Route::post('performance_score_grade/data', [Secure\PerformanceScoreGradeController::class, 'data']);
        Route::get('performance_score_grade/{performance_score_grade}/show', [Secure\PerformanceScoreGradeController::class, 'show']);
        Route::get('performance_score_grade/{performance_score_grade}/edit', [Secure\PerformanceScoreGradeController::class, 'edit']);
        Route::get('performance_score_grade/{performance_score_grade}/delete', [Secure\PerformanceScoreGradeController::class, 'delete']);
        Route::post('performance_score_grade/addEmployees', [Secure\PerformanceScoreGradeController::class, 'addEmployees']);
        Route::resource('performance_score_grade', Secure\PerformanceScoreGradeController::class);


        Route::get('courseCategory/data', [Secure\CourseCategoryController::class, 'data']);
        Route::get('courseCategory/{courseCategory}/show', [Secure\CourseCategoryController::class, 'show']);
        Route::get('courseCategory/{courseCategory}/edit', [Secure\CourseCategoryController::class, 'edit']);
        Route::get('courseCategory/{courseCategory}/delete', [Secure\CourseCategoryController::class, 'delete']);
        Route::resource('learning/courseCategory', Secure\CourseCategoryController::class);




        Route::get('course/data',  [Secure\CourseController::class, 'data']);
        Route::get('course/{course}/show',   [Secure\CourseController::class, 'show']);
        Route::get('course/{course}/edit',   [Secure\CourseController::class, 'edit']);
        Route::get('course/{course}/delete',   [Secure\CourseController::class, 'delete']);
        Route::get('course/comments/{course}',   [Secure\CourseController::class, 'latestCourseUpdates']);
        Route::post('course/addComment',    [Secure\CourseController::class, 'addComment']);
        Route::resource('course', Secure\CourseController::class);



        Route::get('waec_subject/data', [Secure\WaecSubjectController::class, 'data']);
        Route::get('waec_subject/{waecSubject}/show', [Secure\WaecSubjectController::class, 'show']);
        Route::get('waec_subject/{waecSubject}/edit', [Secure\WaecSubjectController::class, 'edit']);
        Route::get('waec_subject/{waecSubject}/delete', [Secure\WaecSubjectController::class, 'delete']);
        Route::resource('waec_subject', Secure\WaecSubjectController::class);

        Route::get('waec_subject_grade/data', [Secure\WaecSubjectGradeController::class, 'data']);
        Route::get('waec_subject_grade/{waecSubjectGrade}/show', [Secure\WaecSubjectGradeController::class, 'show']);
        Route::get('waec_subject_grade/{waecSubjectGrade}/edit', [Secure\WaecSubjectGradeController::class, 'edit']);
        Route::get('waec_subject_grade/{waecSubjectGrade}/delete', [Secure\WaecSubjectGradeController::class, 'delete']);
        Route::resource('waec_subject_grade', Secure\WaecSubjectGradeController::class);

        Route::get('application_type/data', [Secure\ApplicationTypeController::class, 'data']);
        Route::get('application_type/{applicationType}/show', [Secure\ApplicationTypeController::class, 'show']);
        Route::get('application_type/{applicationType}/edit', [Secure\ApplicationTypeController::class, 'edit']);
        Route::get('application_type/{applicationType}/delete', [Secure\ApplicationTypeController::class, 'delete']);
        Route::resource('application_type', Secure\ApplicationTypeController::class);

        //****************routes for accountss*******************************

        Route::get('financial_account/data', [Secure\AccountController::class, 'data']);
        Route::get('financial_account/{financialAccount}/show', [Secure\AccountController::class, 'show']);
        Route::get('financial_account/{financialAccount}/edit', [Secure\AccountController::class, 'edit']);
        Route::get('financial_account/{financialAccount}/delete', [Secure\AccountController::class, 'delete']);

        Route::get('financial_account/chart_of_accounts/{accounts}', function () {
            return view('financial_account.chart_of_accounts');
        });

        Route::resource('financial_account', Secure\AccountController::class);

        Route::get('account_type/data', [Secure\AccountTypeController::class, 'data']);
        Route::get('account_type/{accountType}/show', [Secure\AccountTypeController::class, 'show']);
        Route::get('account_type/{accountType}/edit', [Secure\AccountTypeController::class, 'edit']);
        Route::get('account_type/{accountType}/delete', [Secure\AccountTypeController::class, 'delete']);
        Route::resource('account_type', Secure\AccountTypeController::class);

        Route::get('employee_posting_group/data', [Secure\EmployeePostingGroupController::class, 'data']);
        Route::get('employee_posting_group/{employeePostingGroup}/show', [Secure\EmployeePostingGroupController::class, 'show']);
        Route::get('employee_posting_group/{employeePostingGroup}/edit', [Secure\EmployeePostingGroupController::class, 'edit']);
        Route::get('employee_posting_group/{employeePostingGroup}/delete', [Secure\EmployeePostingGroupController::class, 'delete']);
        Route::resource('employee_posting_group', Secure\EmployeePostingGroupController::class);

        Route::get('journal/data', [Secure\GeneralLedgerController::class, 'data']);
        Route::get('journal/{journal}/show', [Secure\GeneralLedgerController::class, 'show']);
        Route::get('journal/{journal}/edit', [Secure\GeneralLedgerController::class, 'edit']);
        Route::get('journal/{journal}/delete', [Secure\GeneralLedgerController::class, 'delete']);
        Route::resource('journal', Secure\GeneralLedgerController::class);

        Route::post('journal/ajaxAddJournal', [Secure\GeneralLedgerController::class, 'ajaxAddJournal']);
        Route::post('payment/ajaxAddPayment', [Secure\GeneralLedgerController::class, 'ajaxAddPayment']);
        Route::post('journal/DeleteJournal', [Secure\GeneralLedgerController::class, 'DeleteJournal']);

        Route::post('journal/ajaxPostJournal', [Secure\GeneralLedgerController::class, 'ajaxpostJournal']);

        Route::prefix('salary')->group(function () {
            Route::get('data', [Secure\SalaryController::class, 'data']);
            Route::get('{salary}/delete', [Secure\SalaryController::class, 'delete']);
            Route::get('{salary}/show', [Secure\SalaryController::class, 'show']);
            Route::get('{salary}/edit', [Secure\SalaryController::class, 'edit']);
            Route::get('{salary}/print_salary', [Secure\SalaryController::class, 'print_salary']);
        });
        Route::resource('salary', Secure\SalaryController::class);

        Route::prefix('staff_attendance')->group(function () {
            Route::get('import', [Secure\StaffAttendanceController::class, 'getImport']);
            Route::post('import', [Secure\StaffAttendanceController::class, 'postImport']);
            Route::post('dailyExport', [Secure\StaffAttendanceController::class, 'dailyExport']);
            Route::post('filter', [Secure\StaffAttendanceController::class, 'filterAttendance']);
            Route::get('monthlyAttendanceIndex', [Secure\StaffAttendanceController::class, 'monthlyAttendanceIndex']);
            Route::post('monthlyAttendance', [Secure\StaffAttendanceController::class, 'monthlyAttendance']);
            Route::get('weeklyAttendanceIndex', [Secure\StaffAttendanceController::class, 'weeklyAttendanceIndex']);
            Route::post('weeklyAttendance', [Secure\StaffAttendanceController::class, 'weeklyAttendance']);
            Route::post('chartFilter', [Secure\StaffAttendanceController::class, 'chartFilter']);
            Route::get('erpSync', [Secure\StaffAttendanceController::class, 'erpSync']);
            Route::get('{employee}/show', [Secure\StaffAttendanceController::class, 'show']);
            Route::get('{employee}/showEmployee', [Secure\StaffAttendanceController::class, 'show']);
            Route::post('{employee}/showEmployee', [Secure\StaffAttendanceController::class, 'showEmployee']);
            Route::post('{employee}/showEmployeeDays', [Secure\StaffAttendanceController::class, 'showEmployeeDays']);
            Route::post('attendance', [Secure\StaffAttendanceController::class, 'attendanceForDate']);
            Route::post('delete', [Secure\StaffAttendanceController::class, 'deleteattendance']);
            Route::post('add', [Secure\StaffAttendanceController::class, 'addAttendance']);
        });
        Route::resource('staff_attendance', Secure\StaffAttendanceController::class);

        Route::get('staff_leave/{staffLeave}/approve', [Secure\StaffLeaveController::class, 'approveLeave']);
        Route::get('staff_leave/{staffLeave}/no_approve', [Secure\StaffLeaveController::class, 'noApproveLeave']);
        Route::post('staff_leave/addComment', [Secure\StaffLeaveController::class, 'addComment']);

        Route::get('invoice/data', [Secure\InvoiceController::class, 'data']);
        Route::get('invoice/{invoice}/show', [Secure\InvoiceController::class, 'show']);
        Route::get('invoice/{invoice}/edit', [Secure\InvoiceController::class, 'edit']);
        Route::get('invoice/{invoice}/delete', [Secure\InvoiceController::class, 'delete']);
        Route::post('invoice/generalExport', [Secure\InvoiceController::class, 'generalExport']);
        Route::resource('invoice', Secure\InvoiceController::class);

        Route::get('registration/data', [Secure\RegistrationController::class, 'data']);
        Route::get('registration/{studentGroup}/subjects_students', [Secure\RegistrationController::class, 'subjectsStudents']);
        Route::get('registration/{registration}/show', [Secure\RegistrationController::class, 'show']);
        Route::get('registration/{registration}/edit', [Secure\RegistrationController::class, 'edit']);
        Route::get('registration/{registration}/delete', [Secure\RegistrationController::class, 'delete']);

        Route::resource('registration', Secure\RegistrationController::class);

        Route::get('scoreSheet', [Secure\ScoreSheetController::class, 'index']);
        Route::get('scoreSheet/{studentGroup}/subjects_students', [Secure\RegistrationController::class, 'subjectsStudents']);
        Route::get('scoreSheet/{registration}/show', [Secure\RegistrationController::class, 'show']);

        Route::resource('scoreSheet', Secure\ScoreSheetController::class);

        Route::get('broadSheet', [Secure\BroadSheetController::class, 'index']);
        Route::get('broadSheet/{studentGroup}/subjects_students', [Secure\BroadSheetController::class, 'subjectsStudents']);
        Route::get('broadSheet/{registration}/show', [Secure\BroadSheetController::class, 'show']);

        Route::resource('broadSheet', Secure\BroadSheetController::class);

        Route::get('debtor/data', [Secure\DebtorController::class, 'data']);
        Route::get('debtor/{user}/show', [Secure\DebtorController::class, 'show']);
        Route::resource('debtor', Secure\DebtorController::class);

        Route::get('payment/data', [Secure\PaymentController::class, 'data']);
        Route::get('payment/{payment}/show', [Secure\PaymentController::class, 'show']);
        Route::get('payment/{payment}/edit', [Secure\PaymentController::class, 'edit']);
        Route::get('payment/{payment}/delete', [Secure\PaymentController::class, 'delete']);
        Route::resource('payment', Secure\PaymentController::class);
    });

    Route::middleware('has_any_role:teacher,student')->group(function () {
        Route::get('subject_question', [Secure\SubjectQuestionController::class, 'index']);
        Route::post('subject_question', [Secure\SubjectQuestionController::class, 'store']);
        Route::get('subject_question/create', [Secure\SubjectQuestionController::class, 'create']);
        Route::get('subject_question/data/{subject}', [Secure\SubjectQuestionController::class, 'data']);
        Route::get('subject_question/data', [Secure\SubjectQuestionController::class, 'data']);
        Route::get('subject_question/{subjectQuestion}/show', [Secure\SubjectQuestionController::class, 'show']);
        Route::post('subject_question/{subjectQuestion}/replay', [Secure\SubjectQuestionController::class, 'replay']);
    });

    Route::middleware('has_any_role:human_resources,admin,super_admin,accountant,employee')->group(function () {
        Route::prefix('employee')->group(function () {
            Route::get('data', [Secure\EmployeeController::class, 'data']);
            Route::get('inActiveEmployees', [Secure\EmployeeController::class, 'inActiveEmployees']);
            Route::get('employeeDirectory', [Secure\EmployeeController::class, 'employeeDirectory']);
            Route::post('searchEmployee', [Secure\EmployeeController::class, 'searchEmployee']);
            Route::post('data', [Secure\EmployeeController::class, 'data']);
            Route::get('allGroupEmployeesIndex', [Secure\EmployeeController::class, 'allGroupEmployeesIndex']);
            Route::post('allGroupEmployees', [Secure\EmployeeController::class, 'allGroupEmployees']);
            Route::get('{employee}/allGroupEdit', [Secure\EmployeeController::class, 'allGroupEdit']);
            Route::post('sendBscWizard', [Secure\KpiController::class, 'sendBscWizard']);
            Route::post('toggleQualification', [Secure\EmployeeController::class, 'toggleQualification']);
            Route::post('toggleCompetency', [Secure\EmployeeController::class, 'toggleCompetency']);
            Route::post('competencyMatrix', [Secure\EmployeeController::class, 'competencyMatrix']);
            Route::get('competencyMatrix', [Secure\EmployeeController::class, 'competencyMatrixIndex']);
            Route::post('performanceMatrix', [Secure\EmployeeController::class, 'performanceMatrix']);
            Route::get('pending', [Secure\EmployeeController::class, 'pendingApproval']);
            Route::post('generalExport', [Secure\EmployeeController::class, 'generalExport']);
            Route::get('import', [Secure\EmployeeController::class, 'getImport'])->name('employee.import');
            Route::post('import', [Secure\EmployeeController::class, 'postImport']);
            Route::post('finish_import', [Secure\EmployeeController::class, 'finishImport']);
            Route::get('download-template', [Secure\EmployeeController::class, 'downloadExcelTemplate']);
            Route::get('export', [Secure\EmployeeController::class, 'export']);
            Route::get('{employee}/delete', [Secure\EmployeeController::class, 'delete']);
            Route::get('{employee}/staff_leaves_applications', [Secure\StaffLeaveController::class, 'staff_leaves_applications']);
            Route::post('{employee}/processEmployeeDataWizard', [Secure\EmployeeController::class, 'processEmployeeDataWizard']);
            Route::post('{employee}/processWizard', [Secure\KpiController::class, 'processWizard']);
            Route::post('{employee}/processWizardGetKpi', [Secure\KpiController::class, 'processWizardGetKpi']);
            Route::post('{employee}/processWizardGetKras', [Secure\KpiController::class, 'processWizardGetKras']);
            Route::post('{employee}/processWizardGetObjectives', [Secure\KpiController::class, 'processWizardGetObjectives']);
            Route::get('{employee}/ajaxCompetency', [Secure\EmployeeController::class, 'ajaxCompetency']);
            Route::delete('{employee}', [Secure\EmployeeController::class, 'destroy']);
            Route::get('{employee}/show', [Secure\EmployeeController::class, 'show'])->name('employee.show');
            Route::get('{employee}/kpis', [Secure\EmployeeController::class, 'kpis'])->name('employee.kpis');
            Route::get('{employee}/{timeline}/timeLineKpis', [Secure\EmployeeController::class, 'timeLineKpis'])->name('employee.timelinekpis');
            Route::get('{employee}/transfer', [Secure\EmployeeController::class, 'employeeTransfer'])->name('employee.transfer');
            Route::post('employeeTransferStore', [Secure\EmployeeController::class, 'employeeTransferStore'])->name('employee.employeeTransferStore');
            Route::get('{employee}/showBarCode', [Secure\EmployeeController::class, 'showBarCode'])->name('employee.showBarCode');
            Route::get('{employee}/showPayrollComponents', [Secure\EmployeeController::class, 'showPayrollComponents'])->name('employee.showPayrollComponents');
            Route::get('{employee}/addPayrollComponent', [Secure\EmployeeController::class, 'addPayrollComponent'])->name('employee.addPayrollComponent');
            Route::post('storePayrollComponent', [Secure\EmployeeController::class, 'storePayrollComponent'])->name('employee.storePayrollComponent');
            Route::get('{employeePayrollComponent}/editPayrollComponent', [Secure\EmployeeController::class, 'editPayrollComponent'])->name('employee.editPayrollComponent');
            Route::put('{employeePayrollComponent}/updatePayrollComponent', [Secure\EmployeeController::class, 'updatePayrollComponent'])->name('employee.updatePayrollComponent');
            Route::get('{employeePayrollComponent}/deletePayrollComponent', [Secure\EmployeeController::class, 'deletePayrollComponent'])->name('employee.deletePayrollComponent');
            Route::get('{grade}/grade', [Secure\EmployeeController::class, 'competencyGradeEmployeesIndex'])->name('employee.gradeEmployeesIndex');
            Route::get('{grade}/performanceGrade', [Secure\EmployeeController::class, 'performanceGradeEmployeesIndex'])->name('employee.gradeEmployeesIndex');
            Route::post('ajaxNote', [Secure\EmployeeController::class, 'ajaxNote']);
            Route::post('ajaxMakeActive', [Secure\EmployeeController::class, 'ajaxMakeActive']);
            Route::post('ajaxMakeTicketAgent', [Secure\EmployeeController::class, 'makeTicketAgent']);
            Route::post('ajaxMakeInActive', [Secure\EmployeeController::class, 'ajaxMakeInActive']);
            Route::post('ajaxMakeGlobal', [Secure\EmployeeController::class, 'ajaxMakeGlobal']);
            Route::post('ajaxRemoveGlobal', [Secure\EmployeeController::class, 'ajaxRemoveGlobal']);
            Route::get('erpSync', [Secure\EmployeeController::class, 'erpSync']);
            Route::get('employeeDataWizard', [Secure\EmployeeController::class, 'employeeDataWizard']);
        });
        Route::resource('employee', Secure\EmployeeController::class);

        Route::prefix('student')->group(function () {
            Route::get('data', [Secure\StudentController::class, 'data']);
            Route::post('data', [Secure\StudentController::class, 'data']);
            Route::get('pending', [Secure\StudentController::class, 'pendingApproval']);
            Route::post('generalExport', [Secure\StudentController::class, 'generalExport']);
            Route::get('import', [Secure\StudentController::class, 'getImport']);
            Route::post('import', [Secure\StudentController::class, 'postImport']);
            Route::post('finish_import', [Secure\StudentController::class, 'finishImport']);
            Route::get('download-template', [Secure\StudentController::class, 'downloadExcelTemplate']);
            Route::get('export', [Secure\StudentController::class, 'export']);
            Route::get('{student}/delete', [Secure\StudentController::class, 'delete']);
            Route::delete('{student}', [Secure\StudentController::class, 'destroy']);
            Route::get('{student}/show', [Secure\StudentController::class, 'show']);
            Route::get('{student}/upgrade', [Secure\StudentController::class, 'upgrade']);
            Route::post('upgrade', [Secure\StudentController::class, 'storeUpgrade']);
            Route::post('ajaxNote', [Secure\StudentController::class, 'ajaxNote']);
            Route::post('ajaxSMS', [Secure\StudentController::class, 'ajaxSMS']);
            Route::post('ajaxMakeActive', [Secure\StudentController::class, 'ajaxMakeActive']);
            Route::post('discountAward', [Secure\StudentAdmissionController::class, 'discountAward']);
            Route::post('discountAwardAll', [Secure\StudentAdmissionController::class, 'discountAwardAll']);
            Route::post('webService', [Secure\StudentAdmissionController::class, 'webService']);
            Route::post('ajaxAcceptAdmission', [Secure\StudentController::class, 'ajaxAcceptAdmission']);
            Route::post('ajaxStudentApprove', [Secure\StudentController::class, 'ajaxStudentApprove']);
            Route::post('ajaxMakeInActive', [Secure\StudentController::class, 'ajaxMakeInActive']);

            Route::get('{student}/admissionLetter', [Secure\StudentController::class, 'admissionLetter']);
            Route::get('{student}/pdfAdmissionLetter', [Secure\StudentController::class, 'pdfAdmissionLetter']);
            Route::get('{student}/reverse_admission', [Secure\StudentController::class, 'reverse_admission']);
            Route::get('{student}/generateAppFee', [Secure\StudentController::class, 'generateAppFee']);

            Route::resource('deferral', Secure\StudentDeferralController::class);
            Route::resource('admission', Secure\StudentAdmissionController::class);
            Route::post('admission/generalExport', [Secure\StudentAdmissionController::class, 'generalExport']);
            Route::post('all/generalExport', [Secure\StudentAllController::class, 'generalExport']);
            Route::resource('all', Secure\StudentAllController::class);
            Route::resource('graduating', Secure\StudentGraduatingController::class);
            Route::resource('student_drop', Secure\StudentDropController::class);
            Route::resource('alumni', Secure\StudentAlumniController::class);
            Route::post('alumni/generalExport', [Secure\StudentAlumniController::class, 'generalExport']);
        });
        Route::resource('student', Secure\StudentController::class);

        Route::get('deferral/data', [Secure\StudentDeferralController::class, 'data']);
        Route::get('admission/data', [Secure\StudentAdmissionController::class, 'data']);
        Route::get('all/data', [Secure\StudentAllController::class, 'data']);
        Route::get('graduating/data', [Secure\StudentGraduatingController::class, 'data']);
        Route::get('student_drop/data', [Secure\StudentDropController::class, 'data']);
        Route::get('alumni/data', [Secure\StudentAlumniController::class, 'data']);


        Route::prefix('applicant')->group(function () {
            Route::get('data', [Secure\ApplicantController::class, 'data']);
            Route::post('ajaxNote', [Secure\ApplicantController::class, 'ajaxNote']);
            Route::post('ajaxSMS', [Secure\ApplicantController::class, 'ajaxSMS']);
            Route::post('enroll', [Secure\ApplicantController::class, 'enroll']);
            Route::get('import', [Secure\ApplicantController::class, 'getImport']);
            Route::post('import', [Secure\ApplicantController::class, 'postImport']);
            Route::post('finish_import', [Secure\ApplicantController::class, 'finishImport']);
            Route::get('download-template', [Secure\ApplicantController::class, 'downloadExcelTemplate']);
            Route::get('export', [Secure\ApplicantController::class, 'export']);
            Route::get('{applicant}/delete', [Secure\ApplicantController::class, 'delete']);
            Route::delete('{applicant}', [Secure\ApplicantController::class, 'destroy']);
            Route::get('{applicant}/show', [Secure\ApplicantController::class, 'show']);
            Route::post('generalExport', [Secure\ApplicantController::class, 'generalExport']);
        });
        Route::resource('applicant', Secure\ApplicantController::class);
        Route::post('applicant/generalExport', [Secure\ApplicantController::class, 'generalExport']);

        Route::prefix('parent')->group(function () {
            Route::get('data', [Secure\ParentController::class, 'data']);
            Route::get('import', [Secure\ParentController::class, 'getImport']);
            Route::post('import', [Secure\ParentController::class, 'postImport']);
            Route::post('finish_import', [Secure\ParentController::class, 'finishImport']);
            Route::get('download-template', [Secure\ParentController::class, 'downloadExcelTemplate']);
            Route::get('export', [Secure\ParentController::class, 'export']);
            Route::get('{parent_user}/edit', [Secure\ParentController::class, 'edit']);
            Route::get('{parent_user}/delete', [Secure\ParentController::class, 'delete']);
            Route::get('{parent_user}/show', [Secure\ParentController::class, 'show']);
            Route::put('{parent_user}', [Secure\ParentController::class, 'update']);
            Route::delete('{parent_user}', [Secure\ParentController::class, 'destroy']);
        });
        Route::resource('parent', Secure\ParentController::class);

        Route::prefix('human_resource')->group(function () {
            Route::get('data', [Secure\HumanResourceController::class, 'data']);
            Route::get('{human_resource}/edit', [Secure\HumanResourceController::class, 'edit']);
            Route::get('{human_resource}/delete', [Secure\HumanResourceController::class, 'delete']);
            Route::get('{human_resource}/show', [Secure\HumanResourceController::class, 'show']);
        });
        Route::resource('human_resource', Secure\HumanResourceController::class);

        Route::prefix('accountant')->group(function () {
            Route::get('data', [Secure\AccountantController::class, 'data']);
            Route::get('{accountant}/edit', [Secure\AccountantController::class, 'edit']);
            Route::get('{accountant}/delete', [Secure\AccountantController::class, 'delete']);
            Route::get('{accountant}/show', [Secure\AccountantController::class, 'show']);
        });
        Route::resource('accountant', Secure\AccountantController::class);

        Route::prefix('kitchen_admin')->group(function () {
            Route::get('data', [Secure\KitchenAdminController::class, 'data']);
            Route::get('{kitchen_admin}/edit', [Secure\KitchenAdminController::class, 'edit']);
            Route::get('{kitchen_admin}/delete', [Secure\KitchenAdminController::class, 'delete']);
            Route::get('{kitchen_admin}/show', [Secure\KitchenAdminController::class, 'show']);
        });
        Route::resource('kitchen_admin', Secure\KitchenAdminController::class);

        Route::prefix('teacher_duty')->group(function () {
            Route::get('data', [Secure\TeacherDutyController::class, 'data']);
            Route::put('{teacherDuty}', [Secure\TeacherDutyController::class, 'update']);
            Route::delete('{teacherDuty}', [Secure\TeacherDutyController::class, 'destroy']);
            Route::get('{teacherDuty}/edit', [Secure\TeacherDutyController::class, 'edit']);
            Route::get('{teacherDuty}/delete', [Secure\TeacherDutyController::class, 'delete']);
            Route::get('{teacherDuty}/show', [Secure\TeacherDutyController::class, 'show']);
        });
        Route::resource('teacher_duty', Secure\TeacherDutyController::class);
    });

    Route::middleware('has_any_role:kitchen_admin,admin,super_admin')->group(function () {
        Route::prefix('kitchen_staff')->group(function () {
            Route::get('data', [Secure\KitchenStaffController::class, 'data']);
            Route::get('{kitchen_staff}/edit', [Secure\KitchenStaffController::class, 'edit']);
            Route::get('{kitchen_staff}/delete', [Secure\KitchenStaffController::class, 'delete']);
            Route::get('{kitchen_staff}/show', [Secure\KitchenStaffController::class, 'show']);
        });
        Route::resource('kitchen_staff', Secure\KitchenStaffController::class);

        Route::prefix('meal_type')->group(function () {
            Route::get('data', [Secure\MealTypeController::class, 'data']);
            Route::put('{mealType}', [Secure\MealTypeController::class, 'update']);
            Route::delete('{mealType}', [Secure\MealTypeController::class, 'destroy']);
            Route::get('{mealType}/edit', [Secure\MealTypeController::class, 'edit']);
            Route::get('{mealType}/delete', [Secure\MealTypeController::class, 'delete']);
            Route::get('{mealType}/show', [Secure\MealTypeController::class, 'show']);
        });
        Route::resource('meal_type', Secure\MealTypeController::class);
    });
    Route::middleware('has_any_role:super_admin,admin,teacher,student,parent,human_resources,librarian,accountant,kitchen_admin,kitchen_staff')->group(function () {
        Route::get('meal_table', [Secure\MealController::class, 'mealTable']);
        Route::get('teacher_duty_table', [Secure\TeacherDutyController::class, 'teacherDutyTable']);
    });

    Route::middleware('has_any_role:kitchen_admin,kitchen_staff,admin')->group(function () {
        Route::prefix('meal')->group(function () {
            Route::get('data', [Secure\MealController::class, 'data']);
            Route::put('{meal}', [Secure\MealController::class, 'update']);
            Route::delete('{meal}', [Secure\MealController::class, 'destroy']);
            Route::get('{meal}/edit', [Secure\MealController::class, 'edit']);
            Route::get('{meal}/delete', [Secure\MealController::class, 'delete']);
            Route::get('{meal}/show', [Secure\MealController::class, 'show']);
        });
        Route::resource('meal', Secure\MealController::class);
    });

    Route::middleware('has_any_role:teacher,student,parent')->group(function () {
        Route::get('bookuser/index', [Secure\BookUserController::class, 'index']);
        Route::get('bookuser/data', [Secure\BookUserController::class, 'data']);
        Route::get('bookuser/{book}/reserve', [Secure\BookUserController::class, 'reserve']);

        Route::get('borrowedbook/index', [Secure\BorrowedBookController::class, 'index']);
        Route::get('borrowedbook/data', [Secure\BorrowedBookController::class, 'data']);

        Route::get('report/{user}/subjectbook', [Secure\ReportController::class, 'subjectBook']);
        Route::get('report/{user}/getSubjectBook', [Secure\ReportController::class, 'getSubjectBook']);
    });

    //route for teacher and admin users
    Route::middleware('has_any_role:teacher,admin')->group(function () {
        Route::get('notice/data', [Secure\NoticeController::class, 'data']);
        Route::get('notice/{notice}/show', [Secure\NoticeController::class, 'show']);
        Route::get('notice/{notice}/edit', [Secure\NoticeController::class, 'edit']);
        Route::get('notice/{notice}/delete', [Secure\NoticeController::class, 'delete']);
        Route::resource('notice', Secure\NoticeController::class);
    });

    //route for teacher and parent users
    Route::middleware('has_any_role:teacher,parent')->group(function () {
        Route::get('applyingleave/data', [Secure\ApplyingLeaveController::class, 'data']);
        Route::put('applyingleave/{applyingLeave}', [Secure\ApplyingLeaveController::class, 'update']);
        Route::delete('applyingleave/{applyingLeave}', [Secure\ApplyingLeaveController::class, 'destroy']);
        Route::get('applyingleave/{applyingLeave}/edit', [Secure\ApplyingLeaveController::class, 'edit']);
        Route::get('applyingleave/{applyingLeave}/delete', [Secure\ApplyingLeaveController::class, 'delete']);
        Route::get('applyingleave/{applyingLeave}/show', [Secure\ApplyingLeaveController::class, 'show']);
        Route::resource('applyingleave', Secure\ApplyingLeaveController::class);
    });

    Route::middleware('has_any_role:teacher,parent,student,employee')->group(function () {
        Route::get('study_material/data', [Secure\StudyMaterialController::class, 'data']);
        Route::get('study_material/video', [Secure\StudyMaterialController::class, 'video']);
        Route::get('study_material/data/{subject}', [Secure\StudyMaterialController::class, 'data']);
        Route::put('study_material/{studyMaterial}', [Secure\StudyMaterialController::class, 'update']);
        Route::delete('study_material/{studyMaterial}', [Secure\StudyMaterialController::class, 'destroy']);
        Route::get('study_material/{studyMaterial}/download', [Secure\StudyMaterialController::class, 'download']);
        Route::get('study_material/{subject}/subject', [Secure\StudyMaterialController::class, 'subject']);
        Route::get('study_material/{studyMaterial}/show', [Secure\StudyMaterialController::class, 'show']);
        Route::get('study_material/{studyMaterial}/edit', [Secure\StudyMaterialController::class, 'edit']);
        Route::get('study_material/{studyMaterial}/delete', [Secure\StudyMaterialController::class, 'delete']);
        Route::resource('study_material', Secure\StudyMaterialController::class);
    });

    Route::middleware('has_any_role:teacher,admin')->group(function () {
        Route::get('report/index', [Secure\ReportController::class, 'index']);
    });

    //route for teacher users
    Route::middleware('teacher')->group(function () {
        Route::get('teachergroup/data', [Secure\TeacherGroupController::class, 'data']);
        Route::get('teachergroup/timetable', [Secure\TeacherGroupController::class, 'timetable']);
        Route::get('teachergroup/print_timetable', [Secure\TeacherGroupController::class, 'print_timetable']);
        Route::get('teachergroup/{studentGroup}/show', [Secure\TeacherGroupController::class, 'show']);
        Route::get('teachergroup/{subject}/students', [Secure\TeacherGroupController::class, 'students']);
        Route::get('teachergroup/{subject}/studentsdata', [Secure\TeacherGroupController::class, 'students_data']);
        Route::get('teachergroup/{subject}/generate_csv', [Secure\TeacherGroupController::class, 'generateCsvStudentsGroup']);
        Route::get('teachergroup/{subject}/attendance', [Secure\TeacherGroupController::class, 'attendance']);
        Route::get('teachergroup/{subject}/mark', [Secure\TeacherGroupController::class, 'mark']);
        Route::put('teachergroup/{direction}/addstudents', [Secure\TeacherGroupController::class, 'addstudents']);
        Route::get('teachergroup/{direction}/subjects', [Secure\TeacherGroupController::class, 'subjects']);

        Route::put('teachergroup/{direction}/addeditsubject', [Secure\TeacherGroupController::class, 'addeditsubject']);
        Route::get('teachergroup/{direction}/grouptimetable', [Secure\TeacherGroupController::class, 'grouptimetable']);
        Route::post('teachergroup/addtimetable', [Secure\TeacherGroupController::class, 'addtimetable']);
        Route::delete('teachergroup/deletetimetable', [Secure\TeacherGroupController::class, 'deletetimetable']);
        Route::resource('teachergroup', Secure\TeacherGroupController::class);

        Route::get('diary/{diary}/edit', [Secure\DairyController::class, 'edit']);
        Route::get('diary/{diary}/delete', [Secure\DairyController::class, 'delete']);

        Route::get('exam/data', [Secure\ExamController::class, 'data']);
        Route::get('exam/{exam}/show', [Secure\ExamController::class, 'show']);
        Route::get('exam/{exam}/edit', [Secure\ExamController::class, 'edit']);
        Route::get('exam/{exam}/delete', [Secure\ExamController::class, 'delete']);
        Route::resource('exam', Secure\ExamController::class);

        Route::get('teacherstudent/data', [Secure\TeacherEmployeeController::class, 'data']);
        Route::get('teacherstudent/{student}/behavior', [Secure\TeacherEmployeeController::class, 'behavior']);
        Route::post('teacherstudent/{student}/changebehavior', [Secure\TeacherEmployeeController::class, 'change_behavior']);
        Route::get('teacherstudent/{student}/show', [Secure\TeacherEmployeeController::class, 'show']);
        Route::resource('teacherstudent', 'Secure\TeacherEmployeeController');

        Route::post('mark/exams', [Secure\MarkController::class, 'examsForSubject']);
        Route::post('mark/marks', [Secure\MarkController::class, 'marksForSubjectAndDate']);
        Route::post('mark/mark_values', [Secure\MarkController::class, 'markValuesForSubject']);
        Route::post('mark/delete', [Secure\MarkController::class, 'deleteMark']);
        Route::post('mark/add', [Secure\MarkController::class, 'addmark']);
        Route::resource('mark', Secure\MarkController::class);

        Route::post('attendance/attendance', [Secure\AttendanceController::class, 'attendanceForDate']);
        Route::post('attendance/hoursfordate', [Secure\AttendanceController::class, 'hoursForDate']);
        Route::post('attendance/sessionstudents', [Secure\AttendanceController::class, 'sessionstudents']);
        Route::post('attendance/delete', [Secure\AttendanceController::class, 'deleteattendance']);
        Route::post('attendance/add', [Secure\AttendanceController::class, 'addAttendance']);
        Route::resource('attendance', Secure\AttendanceController::class);

        Route::get('exam_attendance/{exam}/data', [Secure\ExamAttendanceController::class, 'data']);
        Route::post('exam_attendance/{exam}/attendance', [Secure\ExamAttendanceController::class, 'getAttendance']);
        Route::post('exam_attendance/{exam}/delete', [Secure\ExamAttendanceController::class, 'deleteAttendance']);
        Route::post('exam_attendance/{exam}/add', [Secure\ExamAttendanceController::class, 'addAttendance']);
        Route::get('exam_attendance/{exam}', [Secure\ExamAttendanceController::class, 'index']);

        Route::prefix('online_exam')->group(function () {
            Route::get('data', [Secure\OnlineExamController::class, 'data']);
            Route::get('download-template', [Secure\OnlineExamController::class, 'downloadExcelTemplate']);
            Route::delete('{onlineExam}', [Secure\OnlineExamController::class, 'destroy']);
            Route::put('{onlineExam}', [Secure\OnlineExamController::class, 'update']);
            Route::get('{onlineExam}/edit', [Secure\OnlineExamController::class, 'edit']);
            Route::get('{onlineExam}/delete', [Secure\OnlineExamController::class, 'delete']);
            Route::get('{onlineExam}/show', [Secure\OnlineExamController::class, 'show']);
            Route::get('{onlineExam}/show_results', [Secure\OnlineExamController::class, 'showResults']);
            Route::get('{onlineExam}/export_questions', [Secure\OnlineExamController::class, 'exportQuestions']);
            Route::get('{onlineExam}/{user}/details', [Secure\OnlineExamController::class, 'showResultDetails']);
        });
        Route::resource('online_exam', Secure\OnlineExamController::class);

        Route::prefix('attendances_for_section')->group(function () {
            Route::post('hoursfordate', [Secure\AttendancesForSectionController::class, 'hoursForDate']);
            Route::post('add', [Secure\AttendancesForSectionController::class, 'addAttendance']);
            Route::post('attendance', [Secure\AttendancesForSectionController::class, 'attendanceForDate']);
            Route::get('students/{section}', [Secure\AttendancesForSectionController::class, 'students']);
        });
        Route::resource('attendances_for_section', Secure\AttendancesForSectionController::class);
    });

    Route::middleware('has_any_role:teacher,human_resources,librarian,admin,employee')->group(function () {
        Route::prefix('leave_management')->group(function () {
            Route::get('staff_leave_plan', [Secure\StaffLeavePlanController::class, 'index']);
            Route::get('index', [Secure\StaffLeaveController::class, 'index']);
            Route::get('approvals', [Secure\StaffLeaveController::class, 'approvals']);
            Route::post('calculateDays', [Secure\StaffLeaveController::class, 'calculateDays']);
            Route::get('allCompanyLeaves', [Secure\StaffLeaveController::class, 'allCompanyLeaves']);
            Route::get('staffLeaveDays', [Secure\StaffLeaveController::class, 'staffLeaveDays']);
            Route::get('createRecord', [Secure\StaffLeaveController::class, 'createRecord']);
            Route::post('storeRecord', [Secure\StaffLeaveController::class, 'storeRecord']);
            Route::post('addComment', [Secure\StaffLeaveController::class, 'addComment']);
            Route::get('{staffLeave}/edit', [Secure\StaffLeaveController::class, 'edit']);
            Route::get('{staffLeave}/delete', [Secure\StaffLeaveController::class, 'delete']);
            Route::get('{staffLeave}/show', [Secure\StaffLeaveController::class, 'show']);
            Route::get('{staffLeave}/approve', [Secure\StaffLeaveController::class, 'approveLeave']);
            Route::get('{staffLeave}/showApprove', [Secure\StaffLeaveController::class, 'showApprove']);
            Route::get('{staffLeave}/modalShowApprove', [Secure\StaffLeaveController::class, 'modalShowApprove']);
            Route::get('{staffLeave}/recallLeave', [Secure\StaffLeaveController::class, 'recallLeave']);
            Route::delete('{staffLeave}', [Secure\StaffLeaveController::class, 'destroy']);
            Route::get('approvals', [Secure\StaffLeaveController::class, 'approvals']);
            Route::put('{staffLeave}', [Secure\StaffLeaveController::class, 'update']);
            Route::get('test', [Secure\StaffLeaveController::class, 'employeeOutstandingLeavedays']);
        });
//TO BE DELETED LATER
        Route::prefix('staff_leave')->group(function () {
            Route::get('approvals', [Secure\StaffLeaveController::class, 'approvals']);
        });
        Route::resource('leave_management', Secure\StaffLeaveController::class);

        Route::prefix('staff_leave_plan')->group(function () {
            Route::get('data', [Secure\StaffLeaveController::class, 'data']);
            Route::post('calculateDays', [Secure\StaffLeaveController::class, 'calculateDays']);
            Route::get('{staffLeavePlan}/edit', [Secure\StaffLeavePlanController::class, 'edit']);
            Route::get('{staffLeavePlan}/delete', [Secure\StaffLeavePlanController::class, 'delete']);
            Route::get('{staffLeavePlan}/show', [Secure\StaffLeavePlanController::class, 'show']);
            Route::delete('{staffLeavePlan}', [Secure\StaffLeavePlanController::class, 'destroy']);
            Route::put('{staffLeavePlan}', [Secure\StaffLeavePlanController::class, 'update']);
        });
        Route::resource('leave_management/staff_leave_plan', Secure\StaffLeavePlanController::class);
    });

    //route for student and parent users
    Route::middleware('has_any_role:student,parent')->group(function () {
        Route::get('studentsection/timetable', [Secure\StudentSectionController::class, 'timetable']);
        Route::get('studentsection/print_timetable', [Secure\StudentSectionController::class, 'print_timetable']);
        Route::get('studentsection/payment', [Secure\StudentSectionController::class, 'payment']);
        Route::get('studentsection/data', [Secure\StudentSectionController::class, 'data']);

        Route::get('report/{user}/marks', [Secure\ReportController::class, 'marks']);
        Route::get('report/{user}/attendances', [Secure\ReportController::class, 'attendances']);
        Route::get('report/{user}/notice', [Secure\ReportController::class, 'notice']);
        Route::post('report/{user}/studentsubjects', [Secure\ReportController::class, 'getStudentSubjects']);
        Route::post('report/{user}/semesters', [Secure\ReportController::class, 'semesters']);
        Route::get('report/{user}/marksforsubject', [Secure\ReportController::class, 'marksForSubject']);
        Route::get('report/{user}/attendancesforsubject', [Secure\ReportController::class, 'attendancesForSubject']);
        Route::get('report/{user}/noticeforsubject', [Secure\ReportController::class, 'noticesForSubject']);
        Route::get('report/{user}/exams', [Secure\ReportController::class, 'exams']);
        Route::get('report/{user}/examforsubject', [Secure\ReportController::class, 'examForSubject']);
        Route::get('report/{user}/online_exams', [Secure\ReportController::class, 'onlineExams']);

        Route::get('payment/{invoice}/pay', [Secure\PaymentController::class, 'pay']);
        Route::post('payment/{invoice}/paypal', [Secure\PaymentController::class, 'paypalPayment']);
        Route::post('payment/{invoice}/stripe', [Secure\PaymentController::class, 'stripe']);
        Route::get('payment/{invoice}/paypal_success', [Secure\PaymentController::class, 'paypalSuccess']);
        Route::get('payment/{invoice}/paypal_cancel', function () {
            return Redirect::to('/');
        });

        Route::get('studentsection/invoice', [Secure\StudentSectionController::class, 'invoice']);
        Route::get('studentsection/invoice/{invoice}/show', [Secure\StudentSectionController::class, 'showInvoice']);
    });

    //route for student user
    Route::middleware('student')->group(function () {
        Route::get('online_exam/{onlineExam}/start', [Secure\OnlineExamController::class, 'startExam']);
        Route::post('online_exam/{onlineExam}/submit_access_code', [Secure\OnlineExamController::class, 'submitAccessCode']);
        Route::post('online_exam/{onlineExam}/submit_answers', [Secure\OnlineExamController::class, 'submitAnswers']);

        Route::get('student_registration/data', [Secure\StudentRegistrationController::class, 'data']);
        Route::get('student_registration/regPrint', [Secure\StudentRegistrationController::class, 'regPrint']);
        Route::get('student_registration/{studentRegistration}/edit', [Secure\StudentRegistrationController::class, 'edit']);
        Route::get('student_registration/{studentRegistration}/delete', [Secure\StudentRegistrationController::class, 'delete']);
        Route::resource('student_registration', Secure\StudentRegistrationController::class);
    });

    //route for parent users
    Route::middleware('parent')->group(function () {
        Route::get('parentsection', [Secure\ParentSectionController::class, 'index']);
        Route::get('parentsection/data', [Secure\ParentSectionController::class, 'data']);
    });

    //route for librarians
    Route::middleware('librarian')->group(function () {
        Route::prefix('book')->group(function () {
            Route::get('data', [Secure\BookController::class, 'data']);
            Route::get('import', [Secure\BookController::class, 'getImport']);
            Route::post('import', [Secure\BookController::class, 'postImport']);
            Route::post('finish_import', [Secure\BookController::class, 'finishImport']);
            Route::get('download-template', [Secure\BookController::class, 'downloadExcelTemplate']);
            Route::get('{book}/show', [Secure\BookController::class, 'show']);
            Route::get('{book}/edit', [Secure\BookController::class, 'edit']);
            Route::get('{book}/delete', [Secure\BookController::class, 'delete']);
        });
        Route::resource('book', Secure\BookController::class);

        Route::get('reservedbook/data', [Secure\ReservedBookController::class, 'data']);
        Route::put('reservedbook/{bookUser}', [Secure\ReservedBookController::class, 'update']);
        Route::delete('reservedbook/{bookUser}', [Secure\ReservedBookController::class, 'destroy']);
        Route::get('reservedbook/{bookUser}/show', [Secure\ReservedBookController::class, 'show']);
        Route::get('reservedbook/{bookUser}/delete', [Secure\ReservedBookController::class, 'delete']);
        Route::get('reservedbook/{bookUser}/issue', [Secure\ReservedBookController::class, 'issue']);
        Route::resource('reservedbook', Secure\ReservedBookController::class);

        Route::get('booklibrarian/issuebook/{user}/{book}/{id}', [Secure\BookLibrarianController::class, 'issueBook']);
        Route::post('booklibrarian/getusers', [Secure\BookLibrarianController::class, 'getUsers']);
        Route::get('booklibrarian/issuereturn/{user}', [Secure\BookLibrarianController::class, 'issueReturnBook']);
        Route::get('booklibrarian/return/{getBook}/{id}', [Secure\BookLibrarianController::class, 'returnBook']);
        Route::post('booklibrarian/getbooks', [Secure\BookLibrarianController::class, 'getBooks']);
        Route::get('booklibrarian/book/{book}', [Secure\BookLibrarianController::class, 'getBook']);
        Route::get('booklibrarian/issue_reserved_book/{bookUser}/{id}', [Secure\BookLibrarianController::class, 'issueReservedBook']);
        Route::get('booklibrarian', [Secure\BookLibrarianController::class, 'index']);
    });
});