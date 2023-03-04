<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\NotificationRequest;
use App\Models\Notification;
use DB;
use Illuminate\Http\Request;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends SecureController
{
    public function __construct()
    {
        parent::__construct();

        view()->share('type', 'notification');

        $columns = ['title', 'date', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('notification.notification');

        return view('notification.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('notification.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(NotificationRequest $request)
    {
        $notification = new Notification($request->all());
        $notification->user_id = Sentinel::getUser()->id;
        $notification->save();

        return redirect('/notification');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Notification $notification)
    {
        $title = trans('notification.details');
        $action = 'show';

        return view('layouts.show', compact('notification', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(Notification $notification)
    {
        $title = trans('notification.edit');

        return view('layouts.edit', compact('title', 'notification'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(NotificationRequest $request, Notification $notification)
    {
        $notification->update($request->all());

        return redirect('/notification');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(Notification $notification)
    {
        $title = trans('notification.delete');

        return view('/notification/delete', compact('notification', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();

        return redirect('/notification');
    }

    public function data()
    {
        $notification = Notification::where('notifications.user_id', $this->user->id)
            ->select(['notifications.id', 'notifications.title', 'notifications.date']);

        return Datatables::make($notification)
            ->addColumn('actions', '<a href="{{ url(\'/notification/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/notification/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/notification/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function getAllData()
    {
        $total = $this->user->notifications()->whereStatus(false)->count();
        $notifications = $this->user->notifications()->latest()->take(5)->whereStatus(false)->get();

        return response()->json(compact('total', 'notifications'), 200);
    }

    public function postRead(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
        ]);

        $model = Notification::find($request->get('id'));
        $model->status = true;
        $model->save();

        return response()->json(['message' => 'Notification updated successfully'], 200);
    }
}
