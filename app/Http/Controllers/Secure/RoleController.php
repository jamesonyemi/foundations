<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Helpers\Settings;
use App\Http\Requests;
use App\Http\Requests\Secure\SchoolRequest;
use App\Models\Company;
use App\Models\Permission;
use App\Repositories\SchoolRepository;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Cartalyst\Sentinel\Roles\EloquentRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends SecureController
{
    public function __construct()
    {
        parent::__construct();

        view()->share('type', 'role');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    //manage permissions
    public function indexPermission()
    {
        $title = 'Permissions';
        $data = [];
        $type = 'permission';
        $permissions = Permission::where('parent_id', 0)->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Permission::where('parent_id', $permission->id)->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }

        return view('permission.index', compact('data', 'title', 'type'));
    }

    public function createPermission()
    {
        $parents = Permission::where('parent_id', 0)->get();
        $type = 'permission';
        $title = 'permission';

        return view('permission._form', compact('parents', 'type', 'title'));
    }

    public function storePermission(Request $request)
    {
        $permission = new Permission();
        $permission->name = $request->name;
        $permission->description = $request->description;
        if (! empty($request->slug)) {
            $permission->slug = $request->slug;
        } else {
            $permission->slug = str_slug($request->name, '_');
        }

        $permission->save();

        return response('<div class="alert alert-success">Permission created Successfully</div>');
    }

    public function editPermission(Permission $permission)
    {
        $type = 'permission';
        $title = 'Edit Permissions';

        return view('permission._form', compact('permission', 'type', 'title'));
    }

    public function showPermission(Permission $permission)
    {
        $type = 'permission';
        $title = 'Permissions';

        return view('permission._details', compact('type', 'permission', 'title'));
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $permission->name = $request->name;
        $permission->description = $request->description;
        if (! empty($request->slug)) {
            $permission->slug = $request->slug;
        } else {
            $permission->slug = str_slug($request->name, '_');
        }
        $permission->save();

        return response('<div class="alert alert-success">Permission saved Successfully</div>');
    }

    //manage roles
    public function indexRole()
    {
        /*if (!Sentinel::hasAccess('student.view')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }*/
        $title = 'Roles';
        $data = EloquentRole::all();

        return view('role.index', compact('data', 'title'));
    }

    public function createRole()
    {
        /*if (!Sentinel::hasAccess('users.roles.create')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }*/
        $title = trans('student.new');
        $data = [];
        $permissions = Permission::where('parent_id', 0)->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Permission::where('parent_id', $permission->id)->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }

        return view('layouts.create', compact('data', 'title'));
    }

    public function storeRole(Request $request)
    {
        /*if (!Sentinel::hasAccess('users.roles.create')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }*/
        $rules = [
            'name' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        } else {
            $role = new EloquentRole();
            $role->name = $request->name;
            $role->slug = GeneralHelper::getUniqueSlug($role, $request->name);
            $role->time_limit = $request->time_limit;
            if ($request->time_limit == 1) {
                if (strtotime($request->from_time) >= strtotime($request->to_time)) {
                    Flash::success('To time must be greater than from time');

                    return redirect()->back()->withInput();
                }
                $role->from_time = $request->from_time;
                $role->to_time = $request->to_time;
                $role->access_days = json_encode($request->access_days);
            } else {
                $role->access_days = json_encode([]);
            }
            $role->save();
            if (! empty($request->permission)) {
                foreach ($request->permission as $key) {
                    $role->updatePermission($key, true, true)->save();
                }
            }
            GeneralHelper::audit_trail('Create Role', 'Users', $role->id);
            Flash::success('Successfully Saved');

            return redirect('user/role/data');
        }
    }

    public function editRole($id)
    {
        /*if (!Sentinel::hasAccess('users.roles.update')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }*/
        $title = 'Edit Role';
        $data = [];
        $permissions = Permission::where('parent_id', 0)->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Permission::where('parent_id', $permission->id)->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }
        $role = EloquentRole::find($id);

        return view('layouts.edit', compact('data', 'role', 'title'));
    }

    public function updateRole(Request $request, $id)
    {
        /*if (!Sentinel::hasAccess('users.roles.update')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }*/
        try {
            $role = EloquentRole::find($id);
            $role->name = $request->name;
            $role->slug = GeneralHelper::getUniqueSlug($role, $request->name);
            $role->time_limit = $request->time_limit;
            if ($request->time_limit == 1) {
                if (strtotime($request->from_time) >= strtotime($request->to_time)) {
                    Flash::warning('To time must be greater than from time');

                    return redirect()->back()->withInput();
                }
                $role->from_time = $request->from_time;
                $role->to_time = $request->to_time;
                $role->access_days = json_encode($request->access_days);
            } else {
                $role->access_days = json_encode([]);
            }
            $role->permissions = [];
            $role->save();
            //remove permissions which have not been ticked
            //create and/or update permissions
            if (! empty($request->permission)) {
                foreach ($request->permission as $key) {
                    $role->updatePermission($key, true, true)->save();
                }
            }

            /* GeneralHelper::audit_trail("Update Role", "Users", $role->id);*/
        /*Flash::success("Successfully Saved");
        return redirect('role');*/
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        if ($role->save()) {
            return response('<div class="alert alert-success">KPI ACTIVITY UPDATED Successfully</div>');
        } else {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    public function deletePermission($id)
    {
        Permission::destroy($id);
        Flash::success('Successfully Saved');

        return redirect('user/permission/data');
    }

    public function deleteRole($id)
    {
        if (! Sentinel::hasAccess('users.roles.delete')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        EloquentRole::destroy($id);
        GeneralHelper::audit_trail('Delete Role', 'Users', $id);
        Flash::success('Successfully Saved');

        return redirect('user/role/data');
    }
}
