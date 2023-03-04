<?php

namespace App\Repositories;

use App\Helpers\Settings;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class InstallRepositoryEloquent implements InstallRepository
{
    public function getRequirements()
    {
        $requirements = [
            'PHP Version (>= 5.6.4)' => version_compare(phpversion(), '5.6.4', '>='),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'PDO Extension' => extension_loaded('PDO'),
            'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'GD Extension' => extension_loaded('gd'),
            'Fileinfo Extension' => extension_loaded('fileinfo'),
        ];

        if (extension_loaded('xdebug')) {
            $requirements['Xdebug Max Nesting Level (>= 500)'] = (int) ini_get('xdebug.max_nesting_level') >= 500;
        }

        return $requirements;
    }

    public function allRequirementsLoaded()
    {
        $allLoaded = true;

        foreach ($this->getRequirements() as $loaded) {
            if ($loaded == false) {
                $allLoaded = false;
            }
        }

        return $allLoaded;
    }

    public function getPermissions()
    {
        return [
            'public/uploads/avatar' => is_writable(public_path('uploads/avatar')),
            'public/uploads/site' => is_writable(public_path('uploads/site')),
            'public/uploads/visitor_card' => is_writable(public_path('uploads/visitor_card')),
            'public/uploads/student_card' => is_writable(public_path('uploads/student_card')),
            'public/uploads/school_photo' => is_writable(public_path('uploads/school_photo')),
            'public/uploads/study_material' => is_writable(public_path('uploads/study_material')),
            'public/uploads/messages' => is_writable(public_path('uploads/messages')),
            'storage/app' => is_writable(storage_path('app')),
            'storage/framework/cache' => is_writable(storage_path('framework/cache')),
            'storage/framework/sessions' => is_writable(storage_path('framework/sessions')),
            'storage/framework/views' => is_writable(storage_path('framework/views')),
            'storage/logs' => is_writable(storage_path('logs')),
            'storage' => is_writable(storage_path('')),
            'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
            '.env file' => is_writable(base_path('.env')),
        ];
    }

    public function allPermissionsGranted()
    {
        $allGranted = true;

        foreach ($this->getPermissions() as $permission => $granted) {
            if ($granted == false) {
                $allGranted = false;
            }
        }

        return $allGranted;
    }

    public function getDisablePermissions()
    {
        return [
            'Base Directory' => ! is_writable(base_path('')),
        ];
    }

    public function allDisablePermissionsGranted()
    {
        $allNotGranted = true;

        foreach ($this->getDisablePermissions() as $permission => $granted) {
            if ($granted == true) {
                $allNotGranted = false;
            }
        }

        return $allNotGranted;
    }

    public function dbCredentialsAreValid($credentials)
    {
        $this->setDatabaseCredentials($credentials);
        $this->reloadEnv();

        try {
            DB::statement('SHOW TABLES');
            /*DB::statement("CREATE TABLE IF NOT EXISTS `settings` (
                              `setting_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                              `setting_value` text COLLATE utf8_unicode_ci NOT NULL
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");*/
        } catch (\Exception $e) {
            Log::info($e->getMessage());

            return false;
        }

        return true;
    }

    private function reloadEnv()
    {
        (new Dotenv(base_path()))->load();
    }

    public function dbDropSettings()
    {
        try {
            DB::statement('DROP TABLE `settings`;');
        } catch (\Exception $e) {
            Log::info($e->getMessage());

            return false;
        }

        return true;
    }

    public function setDatabaseCredentials($credentials)
    {
        $default = config('database.default');

        config([
            "database.connections.{$default}.host" => $credentials['host'],
            "database.connections.{$default}.database" => $credentials['database'],
            "database.connections.{$default}.username" => $credentials['username'],
            "database.connections.{$default}.password" => $credentials['password'],
        ]);

        $path = base_path('.env');
        $env = file($path);

        $env = str_replace('DB_HOST='.env('DB_HOST'), 'DB_HOST='.$credentials['host'].PHP_EOL, $env);
        $env = str_replace('DB_DATABASE='.env('DB_DATABASE'), 'DB_DATABASE='.$credentials['database'].PHP_EOL, $env);
        $env = str_replace('DB_USERNAME='.env('DB_USERNAME'), 'DB_USERNAME='.$credentials['username'].PHP_EOL, $env);
        $env = str_replace('DB_PASSWORD='.env('DB_PASSWORD'), 'DB_PASSWORD='.$credentials['password'].PHP_EOL, $env);

        file_put_contents($path, $env);
    }

    public function setEnvatoCredentials($credentials)
    {
        session(['envato_email' => $credentials->envato_email]);
        session(['purchase_code' => $credentials->purchase_code]);
        session(['envato_username' => $credentials->envato_username]);
        session(['envato' => $credentials->envato]);
    }

    public function getEnvatoCredentials()
    {
        Settings::set('envato_email', session('envato_email'));
        Settings::set('purchase_code', session('purchase_code'));
        Settings::set('envato_username', session('envato_username'));
        Settings::set('envato', session('envato'));
    }

    public function setNatureDevCredentials($credentials)
    {
        session(['secret' => $credentials->secret]);
        session(['license' => $credentials->license]);
        session(['email' => $credentials->email]);
        session(['envato' => $credentials->envato]);
    }

    public function getNatureDevCredentials()
    {
        Settings::set('secret', session('secret'));
        Settings::set('license', session('license'));
        Settings::set('email', session('email'));
        Settings::set('envato', session('envato'));
    }
}
