<?php

namespace App\Http\Controllers;

use App\Helpers\EnvatoValidator;
use App\Helpers\NatureDevValidator;
use App\Helpers\Settings;
use App\Http\Requests\InstallSettingsEmailRequest;
use App\Http\Requests\InstallSettingsRequest;
use App\Http\Requests\VerifyRequest;
use App\Models\Company;
use App\Models\Option;
use App\Models\Permission;
use App\Models\SchoolAdmin;
use App\Repositories\InstallRepository;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Swift_SmtpTransport;
use Swift_TransportException;

class InstallController extends Controller
{
    /**
     * @var InstallRepository
     */
    private $installRepository;

    /**
     * InstallController constructor.
     * @param InstallRepository $installRepository
     */
    public function __construct(InstallRepository $installRepository)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(1000000);
        $this->installRepository = $installRepository;
    }

    public function index()
    {
        $steps = [
            'welcome' => 'active', ];

        return view('install.start', compact('steps'));
    }

    public function requirements()
    {
        $requirements = $this->installRepository->getRequirements();
        $allLoaded = $this->installRepository->allRequirementsLoaded();

        $steps = [
            'welcome' => 'success_step',
            'requirements' => 'active',
        ];

        return view('install.requirements', compact('requirements', 'allLoaded', 'steps'));
    }

    public function permissions()
    {
        if (! $this->installRepository->allRequirementsLoaded()) {
            return redirect('install/requirements');
        }

        $folders = $this->installRepository->getPermissions();
        $allGranted = $this->installRepository->allPermissionsGranted();

        $steps = [
            'welcome' => 'success_step',
            'requirements' => 'success_step',
            'permissions' => 'active',
        ];

        return view('install.permissions', compact('folders', 'allGranted', 'steps'));
    }

    public function verify()
    {
        if (! $this->installRepository->allRequirementsLoaded()) {
            return redirect('install/requirements');
        }

        if (! $this->installRepository->allPermissionsGranted()) {
            return redirect('install/permissions');
        }

        $steps = [
            'welcome' => 'success_step',
            'requirements' => 'success_step',
            'permissions' => 'success_step',
            'verify' => 'active',
        ];

        return view('install.verify', compact('steps'));
    }

    public function verifyApplication(VerifyRequest $request)
    {
        if ($request->get('envato') == 'no') {
            if (NatureDevValidator::is_connected()) {
                $verify = NatureDevValidator::installPurchase($request);
                if ($verify['status'] == 'success') {
                    $this->installRepository->setNatureDevCredentials($request);

                    return redirect('install/database');
                }

                return redirect()->back()->withErrors(['message' => $verify['message']]);
            }
        } else {
            if (EnvatoValidator::is_connected()) {
                $verify = EnvatoValidator::installPurchase($request);
                if ($verify['status'] == 'success') {
                    $this->installRepository->setEnvatoCredentials($request);

                    return redirect('install/database');
                }

                return redirect()->back()->withErrors(['message' => $verify['message']]);
            }
        }

        return redirect()->back()->withErrors(['message'=>trans('verify.no_internet')]);
    }

    public function database()
    {
        if (! $this->installRepository->allRequirementsLoaded()) {
            return redirect('install/requirements');
        }

        if (! $this->installRepository->allPermissionsGranted()) {
            return redirect('install/permissions');
        }

        $steps = [
            'welcome' => 'success_step',
            'requirements' => 'success_step',
            'permissions' => 'success_step',
            'verify' => 'success_step',
            'database' => 'active',
        ];

        return view('install.database', compact('steps'));
    }

    public function installation(Request $request)
    {
        if (! $this->installRepository->allRequirementsLoaded()) {
            return redirect('install/requirements');
        }

        if (! $this->installRepository->allPermissionsGranted()) {
            return redirect('install/permissions');
        }
        $link = @mysqli_connect($request->host, $request->username, $request->password);

        if (! $link) {
            return back()->withErrors('Connection could not be established!!');
        } else {
            if (mysqli_select_db($link, $request->database)) {
                $dbCredentials = $request->only('host', 'username', 'password', 'database');

                //copy(base_path('.env.example'), base_path('.env'));
                $this->installRepository->setDatabaseCredentials($dbCredentials);
            } else {
                return back()->withErrors('Could not select database');
            }
        }
        $steps = [
            'welcome' => 'success_step',
            'requirements' => 'success_step',
            'permissions' => 'success_step',
            'verify' => 'success_step',
            'database' => 'success_step',
            'installation' => 'active',
        ];

        return view('install.installation', compact('steps'));
    }

    public function install()
    {
        try {
            Artisan::call('key:generate');

            if (file_exists(base_path().'/database/migrations/2016_10_16_075226_create_taggable_table.php')) {
                unlink(base_path().'/database/migrations/2016_10_16_075226_create_taggable_table.php');
            }
            if (file_exists(base_path().'\database\migrations\2016_10_16_075226_create_taggable_table.php')) {
                unlink(base_path().'\database\migrations\2016_10_16_075226_create_taggable_table.php');
            }

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            if (session('envato') == 'no') {
                $this->installRepository->getNatureDevCredentials();
            } else {
                $this->installRepository->getEnvatoCredentials();
            }

            return redirect('install/settings');
        } catch (\Exception $e) {
            //@unlink(base_path('.env'));
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect('install/error');
        }
    }

    public function disable()
    {
        $foldersDisable = $this->installRepository->getDisablePermissions();
        $allDisableGranted = $this->installRepository->allDisablePermissionsGranted();
        $steps = [
            'welcome' => 'success_step',
            'requirements' => 'success_step',
            'permissions' => 'success_step',
            'verify' => 'success_step',
            'database' => 'success_step',
            'installation' => 'success_step',
            'settings' => 'active',
        ];

        return view('install.disable', compact('foldersDisable', 'allDisableGranted', 'steps'));
    }

    public function settings()
    {
        $steps = [
            'welcome' => 'success_step',
            'requirements' => 'success_step',
            'permissions' => 'success_step',
            'verify' => 'success_step',
            'database' => 'success_step',
            'installation' => 'success_step',
            'settings' => 'active',
        ];

        $currency = Option::where('category', 'currency')->pluck('title', 'value')->toArray();

        return view('install.settings', compact('currency', 'steps'));
    }

    public function settingsSave(InstallSettingsRequest $request)
    {
        Settings::set('currency', $request->currency);

        Settings::set('multi_school', $request->multi_school);

        Settings::set('date_format', 'Y-d-m');

        Settings::set('time_format', 'g:i a');

        Settings::set('jquery_date', 'GGGG-DD-MM');

        Settings::set('jquery_date_time', 'GGGG-DD-MM h:mm a');

        $super_admin = Sentinel::registerAndActivate([
            'email' => $request->email,
            'password' => $request->password,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'gender' => 1,
        ]);

        if ($request->multi_school == 'no') {
            $role = Sentinel::findRoleBySlug('admin_super_admin');
            $role->users()->attach($super_admin);

            $school = new Company($request->only('title', 'phone', 'address'));
            $school->email = $request->school_email;
            $school->save();

            $school_admin = new SchoolAdmin();
            $school_admin->user_id = $super_admin->id;
            $school_admin->company_id = $school->id;
            $school_admin->save();

            $permissions = Permission::where('role_id', '2')
                ->orderBy('group_name')->orderBy('id')
                ->distinct()->select('group_slug', 'name')
                ->get()->toArray();

            foreach ($permissions as $permission) {
                $super_admin->addPermission("'".$permission['group_slug'].'.'.$permission['name']."'");
                $super_admin->save();
            }
        } else {
            $role = Sentinel::findRoleBySlug('super_admin');
            $role->users()->attach($super_admin);
        }

        return redirect('install/email_settings');
    }

    public function settingsEmail()
    {
        $steps = [
            'welcome' => 'success_step',
            'requirements' => 'success_step',
            'permissions' => 'success_step',
            'verify' => 'success_step',
            'database' => 'success_step',
            'installation' => 'success_step',
            'settings' => 'success_step',
            'mail_settings' => 'active', ];

        return view('install.mail_settings', compact('steps'));
    }

    public function settingsEmailSave(InstallSettingsEmailRequest $request)
    {
        try {
            if ($request->email_driver == 'smtp') {
                $transport = Swift_SmtpTransport::newInstance($request->email_host, $request->email_port, 'ssl');
                $transport->setUsername($request->email_username);
                $transport->setPassword($request->email_password);
                $mailer = \Swift_Mailer::newInstance($transport);
                $mailer->getTransport()->start();
            }
            foreach ($request->except('_token') as $key => $value) {
                Settings::set($key, $value);
            }
            file_put_contents(storage_path('installed'), 'Welcome to SMS');

            return redirect('install/complete');
        } catch (Swift_TransportException $e) {
            return redirect()->back()->withErrors($e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function complete(Request $request)
    {
        if (Settings::get('envato') == 'no') {
            if (NatureDevValidator::is_connected()) {
                $response = NatureDevValidator::complete($request);
                if (isset($response['status']) && $response['status'] == 'success') {
                    $steps = [
                        'welcome'       => 'success_step',
                        'requirements'  => 'success_step',
                        'permissions'   => 'success_step',
                        'verify'        => 'success_step',
                        'database'      => 'success_step',
                        'installation'  => 'success_step',
                        'settings'      => 'success_step',
                        'mail_settings' => 'success_step',
                        'complete'      => 'active',
                    ];

                    return view('install.complete', compact('steps'));
                }
                unlink(storage_path('installed'));

                return redirect()->to('/install');
            }
        } else {
            if (EnvatoValidator::is_connected()) {
                $response = EnvatoValidator::complete($request);
                if (isset($response['status']) && $response['status'] == 'success') {
                    $steps = [
                        'welcome' => 'success_step',
                        'requirements' => 'success_step',
                        'permissions' => 'success_step',
                        'verify' => 'success_step',
                        'database' => 'success_step',
                        'installation' => 'success_step',
                        'settings' => 'success_step',
                        'mail_settings' => 'success_step',
                        'complete' => 'active', ];

                    return view('install.complete', compact('steps'));
                }
                unlink(storage_path('installed'));

                return redirect()->to('/install');
            }
        }

        return redirect()->back();
    }

    public function error()
    {
        return view('install.error');
    }
}
