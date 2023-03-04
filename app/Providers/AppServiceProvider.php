<?php

namespace App\Providers;

use App\Helpers\Settings;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\ApplicationType;
use App\Models\Applicant;
use App\Models\Applicant_school;
use App\Models\Applicant_work;
use App\Models\Applicant_doc;
use App\Models\ApplyingLeave;
use App\Models\Attendance;
use App\Models\Behavior;
use App\Models\Book;
use App\Models\BookUser;
use App\Models\Campus;
use App\Models\Committee;
use App\Models\Company;
use App\Models\CompanyYear;
use App\Models\ConferenceDay;
use App\Models\ConferenceSession;
use App\Models\Country;
use App\Models\CourseCategory;
use App\Models\CourseCategoryProgram;
use App\Models\CourseCategorySection;
use App\Models\Currency;
use App\Models\DailyActivity;
use App\Models\Department;
use App\Models\Diary;
use App\Models\Direction;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\EntryMode;
use App\Models\FeeCategory;
use App\Models\Feedback;
use App\Models\FeesPeriod;
use App\Models\FeesStatus;
use App\Models\GraduationYear;
use App\Models\HelpDesk;
use App\Models\HrPolicy;
use App\Models\IntakePeriod;
use App\Models\Invoice;
use App\Models\JoinDate;
use App\Models\Journal;
use App\Models\Kpi;
use App\Models\Kra;
use App\Models\Level;
use App\Models\LoginHistory;
use App\Models\MaritalStatus;
use App\Models\Mark;
use App\Models\MarkSystem;
use App\Models\MarkType;
use App\Models\MarkValue;
use App\Models\Message;
use App\Models\Notice;
use App\Models\NoticeType;
use App\Models\Notification;
use App\Models\OnlineExam;
use App\Models\Option;
use App\Models\Page;
use App\Models\ParentStudent;
use App\Models\Payment;
use App\Models\Qualification;
use App\Models\Registration;
use App\Models\Religion;
use App\Models\Salary;
use App\Models\Scholarship;
use App\Models\SchoolDirection;
use App\Models\Semester;
use App\Models\Session;
use App\Models\SessionAttendance;
use App\Models\SmsMessage;
use App\Models\StaffAttendance;
use App\Models\StaffLeaveType;
use App\Models\StaffSalary;
use App\Models\Student;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\SubjectQuestion;
use App\Models\TeacherDuty;
use App\Models\TeacherSchool;
use App\Models\TeacherSubject;
use App\Models\Timetable;
use App\Models\TimetablePeriod;
use App\Models\Union;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VisitorLog;
use App\Repositories\AccountRepository;
use App\Repositories\AccountRepositoryEloquent;
use App\Repositories\AccountTypeRepository;
use App\Repositories\AccountTypeRepositoryEloquent;
use App\Repositories\ApplicantDocRepository;
use App\Repositories\ApplicantDocRepositoryEloquent;
use App\Repositories\ApplicantRepository;
use App\Repositories\ApplicantRepositoryEloquent;
use App\Repositories\ApplicantSchoolRepository;
use App\Repositories\ApplicantSchoolRepositoryEloquent;
use App\Repositories\ApplicantWorkRepository;
use App\Repositories\ApplicantWorkRepositoryEloquent;
use App\Repositories\ApplicationTypeRepository;
use App\Repositories\ApplicationTypeRepositoryEloquent;
use App\Repositories\ApplyingLeaveRepository;
use App\Repositories\ApplyingLeaveRepositoryEloquent;
use App\Repositories\AttendanceRepository;
use App\Repositories\AttendanceRepositoryEloquent;
use App\Repositories\BehaviorRepository;
use App\Repositories\BehaviorRepositoryEloquent;
use App\Repositories\BookRepository;
use App\Repositories\BookRepositoryEloquent;
use App\Repositories\BookUserRepository;
use App\Repositories\BookUserRepositoryEloquent;
use App\Repositories\CampusRepository;
use App\Repositories\CampusRepositoryEloquent;
use App\Repositories\CertificateRepository;
use App\Repositories\CertificateRepositoryEloquent;
use App\Repositories\CommitteeRepository;
use App\Repositories\CommitteeRepositoryEloquent;
use App\Repositories\ConferenceDayRepository;
use App\Repositories\ConferenceDayRepositoryEloquent;
use App\Repositories\ConferenceSessionRepository;
use App\Repositories\ConferenceSessionRepositoryEloquent;
use App\Repositories\CountryRepository;
use App\Repositories\CountryRepositoryEloquent;
use App\Repositories\CourseCategoryDirectionRepository;
use App\Repositories\CourseCategoryDirectionRepositoryEloquent;
use App\Repositories\CourseCategoryRepository;
use App\Repositories\CourseCategoryRepositoryEloquent;
use App\Repositories\CourseCategorySectionRepository;
use App\Repositories\CourseCategorySectionRepositoryEloquent;
use App\Repositories\CurrencyRepository;
use App\Repositories\CurrencyRepositoryEloquent;
use App\Repositories\DailyActivityRepository;
use App\Repositories\DailyActivityRepositoryEloquent;
use App\Repositories\DiaryRepository;
use App\Repositories\DiaryRepositoryEloquent;
use App\Repositories\DirectionRepository;
use App\Repositories\DirectionRepositoryEloquent;

use App\Repositories\EmployeeRepository;
use App\Repositories\EmployeeRepositoryEloquent;
use App\Repositories\EmployeeShiftRepository;
use App\Repositories\EmployeeShiftRepositoryEloquent;
use App\Repositories\EntryModeRepository;
use App\Repositories\EntryModeRepositoryEloquent;

use App\Repositories\ExcelRepository;
use App\Repositories\ExcelRepositoryDefault;
use App\Repositories\FeeCategoryRepository;
use App\Repositories\FeeCategoryRepositoryEloquent;
use App\Repositories\FeedbackRepository;
use App\Repositories\FeedbackRepositoryEloquent;
use App\Repositories\FeesPeriodRepository;
use App\Repositories\FeesPeriodRepositoryEloquent;
use App\Repositories\FeesStatusRepository;
use App\Repositories\FeesStatusRepositoryEloquent;

use App\Repositories\GraduationYearRepository;
use App\Repositories\GraduationYearRepositoryEloquent;
use App\Repositories\HelpDeskRepository;
use App\Repositories\HelpDeskRepositoryEloquent;
use App\Repositories\HrPolicyRepository;
use App\Repositories\HrPolicyRepositoryEloquent;
use App\Repositories\InstallRepository;
use App\Repositories\InstallRepositoryEloquent;
use App\Repositories\IntakePeriodRepository;
use App\Repositories\IntakePeriodRepositoryEloquent;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceRepositoryEloquent;
use App\Repositories\JoinDateRepository;
use App\Repositories\JoinDateRepositoryEloquent;
use App\Repositories\JournalRepository;
use App\Repositories\JournalRepositoryEloquent;
use App\Repositories\KpiRepository;
use App\Repositories\KpiRepositoryEloquent;
use App\Repositories\KraRepository;
use App\Repositories\KraRepositoryEloquent;
use App\Repositories\LevelRepository;
use App\Repositories\LevelRepositoryEloquent;
use App\Repositories\LoginHistoryRepository;
use App\Repositories\LoginHistoryRepositoryEloquent;
use App\Repositories\MaritalStatusRepository;
use App\Repositories\MaritalStatusRepositoryEloquent;
use App\Repositories\MarkRepository;
use App\Repositories\MarkRepositoryEloquent;
use App\Repositories\MarkSystemRepository;
use App\Repositories\MarkSystemRepositoryEloquent;
use App\Repositories\MarkTypeRepository;
use App\Repositories\MarkTypeRepositoryEloquent;
use App\Repositories\MarkValueRepository;
use App\Repositories\MarkValueRepositoryEloquent;

use App\Repositories\MessageRepository;
use App\Repositories\MessageRepositoryEloquent;
use App\Repositories\NoticeRepository;
use App\Repositories\NoticeRepositoryEloquent;
use App\Repositories\NoticeTypeRepository;
use App\Repositories\NoticeTypeRepositoryEloquent;
use App\Repositories\NotificationRepository;
use App\Repositories\NotificationRepositoryEloquent;
use App\Repositories\OnlineExamRepository;
use App\Repositories\OnlineExamRepositoryEloquent;
use App\Repositories\OptionRepository;
use App\Repositories\OptionRepositoryEloquent;
use App\Repositories\PageRepository;
use App\Repositories\PageRepositoryEloquent;
use App\Repositories\ParentStudentRepository;
use App\Repositories\ParentStudentRepositoryEloquent;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentRepositoryEloquent;
use App\Repositories\QualificationRepository;
use App\Repositories\QualificationRepositoryEloquent;
use App\Repositories\RegistrationRepository;
use App\Repositories\RegistrationRepositoryEloquent;
use App\Repositories\ReligionRepository;
use App\Repositories\ReligionRepositoryEloquent;

use App\Repositories\SalaryRepository;
use App\Repositories\SalaryRepositoryEloquent;
use App\Repositories\ScholarshipRepository;
use App\Repositories\ScholarshipRepositoryEloquent;
use App\Repositories\SchoolDirectionRepository;
use App\Repositories\SchoolDirectionRepositoryEloquent;
use App\Repositories\SchoolRepository;
use App\Repositories\SchoolRepositoryEloquent;
use App\Repositories\SchoolYearRepository;
use App\Repositories\SchoolYearRepositoryEloquent;
use App\Repositories\SectionRepository;
use App\Repositories\SectionRepositoryEloquent;
use App\Repositories\SemesterRepository;
use App\Repositories\SemesterRepositoryEloquent;
use App\Repositories\SessionAttendanceRepository;
use App\Repositories\SessionAttendanceRepositoryEloquent;
use App\Repositories\SessionRepository;
use App\Repositories\SessionRepositoryEloquent;
use App\Repositories\SmsMessageRepository;
use App\Repositories\SmsMessageRepositoryEloquent;
use App\Repositories\StaffAttendanceRepository;
use App\Repositories\StaffAttendanceRepositoryEloquent;
use App\Repositories\StaffLeaveTypeRepository;
use App\Repositories\StaffLeaveTypeRepositoryEloquent;
use App\Repositories\StaffSalaryRepository;
use App\Repositories\StaffSalaryRepositoryEloquent;

use App\Repositories\StudentRepository;
use App\Repositories\StudentRepositoryEloquent;
use App\Repositories\StudyMaterialRepository;
use App\Repositories\StudyMaterialRepositoryEloquent;
use App\Repositories\SubjectQuestionRepository;
use App\Repositories\SubjectQuestionRepositoryEloquent;
use App\Repositories\SubjectRepository;
use App\Repositories\SubjectRepositoryEloquent;
use App\Repositories\TeacherDutyRepository;
use App\Repositories\TeacherDutyRepositoryEloquent;
use App\Repositories\TeacherSchoolRepository;
use App\Repositories\TeacherSchoolRepositoryEloquent;
use App\Repositories\TeacherSubjectRepository;
use App\Repositories\TeacherSubjectRepositoryEloquent;
use App\Repositories\TimetablePeriodRepository;
use App\Repositories\TimetablePeriodRepositoryEloquent;
use App\Repositories\TimetableRepository;
use App\Repositories\TimetableRepositoryEloquent;

use App\Repositories\UnionRepository;
use App\Repositories\UnionRepositoryEloquent;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryEloquent;
use App\Repositories\VendorRepository;
use App\Repositories\VendorRepositoryEloquent;
use App\Repositories\VisitorLogRepository;
use App\Repositories\VisitorLogRepositoryEloquent;
use Cartalyst\Sentinel\Sentinel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Excel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $this->setDbConfigurations();
        app()->useLangPath(base_path('lang'));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (is_dir(base_path().'/public_html')) {
            $this->app->bind('path.public', function () {
                return base_path().'/public_html';
            });
        }


        $this->app->bind(CountryRepository::class, function ($app) {
            return new CountryRepositoryEloquent(new Country());
        });



        $this->app->bind(EmployeeRepository::class, function ($app) {
            return new EmployeeRepositoryEloquent(new Employee());
        });


        $this->app->bind(FeesPeriodRepository::class, function ($app) {
            return new FeesPeriodRepositoryEloquent(new FeesPeriod());
        });

        $this->app->bind(FeesStatusRepository::class, function ($app) {
            return new FeesStatusRepositoryEloquent(new FeesStatus());
        });

        $this->app->bind(IntakePeriodRepository::class, function ($app) {
            return new IntakePeriodRepositoryEloquent(new IntakePeriod());
        });

        $this->app->bind(EmployeeShiftRepository::class, function ($app) {
            return new EmployeeShiftRepositoryEloquent(new EmployeeShift());
        });

        $this->app->bind(HrPolicyRepository::class, function ($app) {
            return new HrPolicyRepositoryEloquent(new HrPolicy());
        });

        $this->app->bind(HelpDeskRepository::class, function ($app) {
            return new HelpDeskRepositoryEloquent(new HelpDesk());
        });

        $this->app->bind(LevelRepository::class, function ($app) {
            return new LevelRepositoryEloquent(new Level());
        });

        $this->app->bind(KraRepository::class, function ($app) {
            return new KraRepositoryEloquent(new Kra());
        });

        $this->app->bind(KpiRepository::class, function ($app) {
            return new KpiRepositoryEloquent(new Kpi());
        });

        $this->app->bind(ApplicationTypeRepository::class, function ($app) {
            return new ApplicationTypeRepositoryEloquent(new ApplicationType());
        });

        $this->app->bind(MaritalStatusRepository::class, function ($app) {
            return new MaritalStatusRepositoryEloquent(new MaritalStatus());
        });

        $this->app->bind(QualificationRepository::class, function ($app) {
            return new QualificationRepositoryEloquent(new Qualification());
        });

        $this->app->bind(RegistrationRepository::class, function ($app) {
            return new RegistrationRepositoryEloquent(new Registration());
        });

        $this->app->bind(ReligionRepository::class, function ($app) {
            return new ReligionRepositoryEloquent(new Religion());
        });

        $this->app->bind(SessionRepository::class, function ($app) {
            return new SessionRepositoryEloquent(new Session());
        });

        $this->app->bind(SessionAttendanceRepository::class, function ($app) {
            return new SessionAttendanceRepositoryEloquent(new SessionAttendance());
        });

        $this->app->bind(JournalRepository::class, function ($app) {
            return new JournalRepositoryEloquent(new Journal());
        });

        $this->app->bind(UnionRepository::class, function ($app) {
            return new UnionRepositoryEloquent(new Union());
        });

        $this->app->bind(VendorRepository::class, function ($app) {
            return new VendorRepositoryEloquent(new Vendor());
        });

        //Mathew additions

        $this->app->bind(ApplyingLeaveRepository::class, function ($app) {
            return new ApplyingLeaveRepositoryEloquent(new ApplyingLeave());
        });
        $this->app->bind(AttendanceRepository::class, function ($app) {
            return new AttendanceRepositoryEloquent(new Attendance());
        });
        $this->app->bind(BehaviorRepository::class, function ($app) {
            return new BehaviorRepositoryEloquent(new Behavior());
        });
        $this->app->bind(BookRepository::class, function ($app) {
            return new BookRepositoryEloquent(new Book());
        });
        $this->app->bind(BookUserRepository::class, function ($app) {
            return new BookUserRepositoryEloquent(new BookUser());
        });

        $this->app->bind(DiaryRepository::class, function ($app) {
            return new DiaryRepositoryEloquent(new Diary());
        });
        $this->app->bind(DirectionRepository::class, function ($app) {
            return new DirectionRepositoryEloquent(new Direction());
        });

        $this->app->bind(FeedbackRepository::class, function ($app) {
            return new FeedbackRepositoryEloquent(new Feedback());
        });
        $this->app->bind(FeeCategoryRepository::class, function ($app) {
            return new FeeCategoryRepositoryEloquent(new FeeCategory());
        });
        $this->app->bind(InvoiceRepository::class, function ($app) {
            return new InvoiceRepositoryEloquent(new Invoice());
        });
        $this->app->bind(JoinDateRepository::class, function ($app) {
            return new JoinDateRepositoryEloquent(new JoinDate());
        });
        $this->app->bind(LoginHistoryRepository::class, function ($app) {
            return new LoginHistoryRepositoryEloquent(new LoginHistory());
        });
        $this->app->bind(MarkRepository::class, function ($app) {
            return new MarkRepositoryEloquent(new Mark());
        });
        $this->app->bind(MarkSystemRepository::class, function ($app) {
            return new MarkSystemRepositoryEloquent(new MarkSystem());
        });
        $this->app->bind(MarkTypeRepository::class, function ($app) {
            return new MarkTypeRepositoryEloquent(new MarkType());
        });

        $this->app->bind(MarkValueRepository::class, function ($app) {
            return new MarkValueRepositoryEloquent(new MarkValue());
        });
        $this->app->bind(MessageRepository::class, function ($app) {
            return new MessageRepositoryEloquent(new Message());
        });
        $this->app->bind(NoticeRepository::class, function ($app) {
            return new NoticeRepositoryEloquent(new Notice());
        });
        $this->app->bind(NoticeTypeRepository::class, function ($app) {
            return new NoticeTypeRepositoryEloquent(new NoticeType());
        });
        $this->app->bind(NotificationRepository::class, function ($app) {
            return new NotificationRepositoryEloquent(new Notification());
        });
        $this->app->bind(OnlineExamRepository::class, function ($app) {
            return new OnlineExamRepositoryEloquent(new OnlineExam());
        });
        $this->app->bind(OptionRepository::class, function ($app) {
            return new OptionRepositoryEloquent(new Option());
        });
        $this->app->bind(SectionRepository::class, function ($app) {
            return new SectionRepositoryEloquent(new Department());
        });
        $this->app->bind(PageRepository::class, function ($app) {
            return new PageRepositoryEloquent(new Page());
        });
        $this->app->bind(ParentStudentRepository::class, function ($app) {
            return new ParentStudentRepositoryEloquent(new ParentStudent());
        });
        $this->app->bind(PaymentRepository::class, function ($app) {
            return new PaymentRepositoryEloquent(new Payment());
        });

        $this->app->bind(SalaryRepository::class, function ($app) {
            return new SalaryRepositoryEloquent(new Salary());
        });
        $this->app->bind(ScholarshipRepository::class, function ($app) {
            return new ScholarshipRepositoryEloquent(new Scholarship());
        });
        $this->app->bind(SchoolDirectionRepository::class, function ($app) {
            return new SchoolDirectionRepositoryEloquent(new SchoolDirection());
        });
        $this->app->bind(SchoolRepository::class, function ($app) {
            return new SchoolRepositoryEloquent(new Company());
        });
        $this->app->bind(SchoolYearRepository::class, function ($app) {
            return new SchoolYearRepositoryEloquent(new CompanyYear());
        });
        $this->app->bind(GraduationYearRepository::class, function ($app) {
            return new GraduationYearRepositoryEloquent(new GraduationYear());
        });
        $this->app->bind(SemesterRepository::class, function ($app) {
            return new SemesterRepositoryEloquent(new Semester());
        });
        $this->app->bind(SmsMessageRepository::class, function ($app) {
            return new SmsMessageRepositoryEloquent(new SmsMessage());
        });
        $this->app->bind(StaffAttendanceRepository::class, function ($app) {
            return new StaffAttendanceRepositoryEloquent(new StaffAttendance());
        });
        $this->app->bind(StaffSalaryRepository::class, function ($app) {
            return new StaffSalaryRepositoryEloquent(new StaffSalary());
        });

        $this->app->bind(StudyMaterialRepository::class, function ($app) {
            return new StudyMaterialRepositoryEloquent(new StudyMaterial());
        });

        $this->app->bind(StudentRepository::class, function ($app) {
            return new StudentRepositoryEloquent(new Student());
        }); $this->app->bind(SubjectRepository::class, function ($app) {
        return new SubjectRepositoryEloquent(new Subject());
        });
        $this->app->bind(TeacherDutyRepository::class, function ($app) {
            return new TeacherDutyRepositoryEloquent(new TeacherDuty());
        });
        $this->app->bind(TeacherSchoolRepository::class, function ($app) {
            return new TeacherSchoolRepositoryEloquent(new TeacherSchool());
        });
        $this->app->bind(TeacherSubjectRepository::class, function ($app) {
            return new TeacherSubjectRepositoryEloquent(new TeacherSubject());
        });
        $this->app->bind(TimetableRepository::class, function ($app) {
            return new TimetableRepositoryEloquent(new Timetable());
        });
        $this->app->bind(TimetablePeriodRepository::class, function ($app) {
            return new TimetablePeriodRepositoryEloquent(new TimetablePeriod());
        });
        $this->app->bind(SubjectQuestionRepository::class, function ($app) {
            return new SubjectQuestionRepositoryEloquent(new SubjectQuestion());
        });
        $this->app->bind(StaffLeaveTypeRepository::class, function ($app) {
            return new StaffLeaveTypeRepositoryEloquent(new StaffLeaveType());
        });

        $this->app->bind(InstallRepository::class, function ($app) {
            return new InstallRepositoryEloquent();
        });

        $this->app->bind(VisitorLogRepository::class, function ($app) {
            return new VisitorLogRepositoryEloquent(new VisitorLog());
        });

        $this->app->bind(ExcelRepository::class, function ($app) {
            $excel = new Excel(
                $app['phpexcel'],
                $app['excel.reader'],
                $app['excel.writer'],
                $app['excel.parsers.view']
            );

            return new ExcelRepositoryDefault($excel);
        });

        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepositoryEloquent(new User());
        });
    }

    private function setDbConfigurations()
    {
        try {
            //Pusher
            config(['broadcasting.connections.pusher.key' => Settings::get('pusher_key')]);
            config(['broadcasting.connections.pusher.secret' => Settings::get('pusher_secret')]);
            config(['broadcasting.connections.pusher.app_id' => Settings::get('pusher_app_id')]);

            //Stripe
            config(['services.stripe.key' => Settings::get('stripe_secret')]);
            config(['services.stripe.secret' => Settings::get('stripe_publishable')]);

            //App Name
            /*config(['services.stripe.secret' => Settings::get('stripe_publishable')]);*/

            //Mailserver
            config(['mail.driver' => ((Settings::get('email_driver') == null) ? Settings::get('email_driver') : 'smtp')]);
            config(['mail.host' => Settings::get('email_host')]);
            config(['mail.port' => Settings::get('email_port')]);
            config(['mail.username' => Settings::get('email_username')]);
            config(['mail.password' => Settings::get('email_password')]);
            config(['mail.from.address' => Settings::get('email')]);
            config(['mail.from.name' => Settings::get('name')]);
            config(['mail.from.encryption' => Settings::get('encryption')]);

            /*
             * SMS Setings
             */
            /*if (Settings::get('sms_driver') == 'msg91') {
            } elseif (Settings::get('sms_driver') == 'bulk_sms') {
                //Bulk-sms
                config([ 'bulk-sms.username' => Settings::get('sms_bulk_username') ]);
                config([ 'bulk-sms.password' => Settings::get('sms_bulk_password') ]);
            } else {
                config([ 'sms.from' => Settings::get('sms_from') ]);
                config([ 'sms.driver' => Settings::get('sms_driver') ]);
                //Callfire
                config([ 'sms.callfire.app_login' => Settings::get('callfire_app_login') ]);
                config([ 'sms.callfire.app_password' => Settings::get('callfire_app_password') ]);
                //Eztexting
                config([ 'sms.eztexting.username' => Settings::get('eztexting_username') ]);
                config([ 'sms.eztexting.password' => Settings::get('eztexting_password') ]);
                //Labsmobile
                config([ 'sms.labsmobile.client' => Settings::get('labsmobile_client') ]);
                config([ 'sms.labsmobile.username' => Settings::get('labsmobile_username') ]);
                config([ 'sms.labsmobile.password' => Settings::get('labsmobile_password') ]);
                //Mozeo
                config([ 'sms.mozeo.company_key' => Settings::get('mozeo_company_key') ]);
                config([ 'sms.mozeo.username' => Settings::get('mozeo_username') ]);
                config([ 'sms.mozeo.password' => Settings::get('mozeo_password') ]);
                //Nexmo
                config([ 'sms.nexmo.api_key' => Settings::get('nexmo_api_key') ]);
                config([ 'sms.nexmo.api_secret' => Settings::get('nexmo_api_secret') ]);
                //Twilio
                config([ 'sms.twilio.account_sid' => Settings::get('twilio_account_sid') ]);
                config([ 'sms.twilio.auth_token' => Settings::get('twilio_auth_token') ]);
                //Zenvia
                config([ 'sms.zenvia.account_key' => Settings::get('zenvia_account_key') ]);
                config([ 'sms.zenvia.passcode' => Settings::get('zenvia_passcode') ]);
            }*/
        } catch (\Exception $e) {
        }
    }
}
