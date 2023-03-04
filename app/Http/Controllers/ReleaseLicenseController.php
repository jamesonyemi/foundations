<?php

namespace App\Http\Controllers;

use App\Helpers\EnvatoValidator;
use App\Helpers\NatureDevValidator;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReleaseLicenseController extends Controller
{
    public function index()
    {
        return view('release_license.index');
    }

    public function releaseLicense(Request $request)
    {
        if (Settings::get('envato') == 'no') {
            if (NatureDevValidator::is_connected()) {
                NatureDevValidator::releaseLicense($request);
                unlink(storage_path('installed'));

                $colname = 'Tables_in_'.env('DB_DATABASE');
                $tables = DB::select('SHOW TABLES');
                foreach ($tables as $table) {
                    $droplist[] = $table->$colname;
                }
                $droplist = implode(',', $droplist);
                DB::beginTransaction();
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                DB::statement("DROP TABLE $droplist");
                DB::commit();

                return redirect()->to('/');
            }
        } else {
            if (EnvatoValidator::is_connected()) {
                EnvatoValidator::releaseLicense($request);
                unlink(storage_path('installed'));

                $colname = 'Tables_in_'.env('DB_DATABASE');
                $tables = DB::select('SHOW TABLES');
                foreach ($tables as $table) {
                    $droplist[] = $table->$colname;
                }
                $droplist = implode(',', $droplist);
                DB::beginTransaction();
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                DB::statement("DROP TABLE $droplist");
                DB::commit();

                return redirect()->to('/');
            }
        }

        return redirect()->back()->withErrors(['message' => ('verify.no_internet')]);
    }
}
