<?php

namespace App\Http\Controllers;


use App\Grade;
use App\Http\Requests;
use App\Http\Requests\UserRequest;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Response;
use URL;
use Webpatser\Countries\Countries;

class UserController extends Controller
{
    protected $currentModelName;

    public function __construct()
    {
//        $this->middleware('auth');
        // Fetch the Site Settings object
        $this->currentModelName = trans_choice('core.user', 1);
        $this->modelPlural = trans_choice('core.user', 1);
        View::share('currentModelName', $this->currentModelName);
        View::share('modelPlural', $this->modelPlural);


    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (Auth::user()->isSuperAdmin()) {
            $users = User::paginate(Config::get('constants.PAGINATION'));

            return view('users.index', compact('users'));
        } else {
            return view('errors.general',
                ['code' => '403',
                    'message' => trans('core.forbidden'),
                    'quote' => trans('msg.access_denied'),
                    'author' => 'Admin',
                    'source' => '',
                ]
            );
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $user = new User();
        $roles = Role::lists('name', 'id');
        $grades = Grade::lists('name', 'id');
        $countries = Countries::lists('name', 'id');
        $submitButton = trans('core.addModel', ['currentModelName' => $this->currentModelName]);
        return view('users.form', compact('user', 'grades', 'countries', 'roles', 'submitButton')); //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return Response
     */
    public function store(UserRequest $request)
    {

        $data = $request->except('_token');

//        $data = User::uploadPic($data);
//        dd(Input::file('avatar'), $destinationPath, $fileName,Input::file('avatar')->move($destinationPath, $fileName));
        if ($request->is("users")) {
            $data['provider'] = "created";
        } else {
            $data['provider'] = "register";
        }

        $data['provider_id'] = $data['email'];
        $data['verified'] = 1;
//        dd($data);
//        $user =
//        $user->avatar = $fileName;

        if (User::create($data)) {
            flash()->success(trans('msg.user_create_successful'));
        } else
            flash()->error(trans('msg.user_create_successful'));
        return redirect(URL::action('UserController@index'));
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return Response
     */
    public function show($user)
    {

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(User $user)
    {
//        $user = User::findOrFail($id);
        $roles = Role::lists('name', 'id');
        $grades = Grade::orderBy('order')->lists('name', 'id');
        $countries = Countries::lists('name', 'id');

        return view('users.edit', compact('user', 'grades', 'countries', 'roles')); //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param User $profile
     * @return Response
     */
    public function update(UserRequest $request, User $user)
    {
        $except = [];
        if (trim(Input::get('role_id')) == '') {
            array_push($except, 'role_id');
        }
        if (trim(Input::get('password')) == '' && trim(Input::get('password_confirmation')) == '') {
            array_push($except, 'password');

        }

//        if (trim(Input::get('avatar')) == '') {
//            array_push($except, 'avatar');
//        }
        array_push($except, '_token');

        $req = $request->except($except);
//        $data = User::uploadPic($req);


        //TODO: Should have an expection for pics


        if ($user->update($req)) {
            flash()->success(trans('msg.user_update_successful'));
        } else
            flash()->success(Lang::get('msg.user_update_error'));


        if ($user->id == Auth::user()->id) {
            return redirect(URL::action('UserController@edit',Auth::user()->slug));
        } else  {
            return redirect(URL::action('UserController@index'));
        }

    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
//    public function showProfile($id)
//    {
//        $user = User::findOrFail($id);
//        $grades = Grade::lists('name', 'id');
//        $countries = Countries::lists('name', 'id');
//        return view('users.show', compact('user', 'countries', 'grades'));
//    }

    public function exportUsersExcel()
    {

        Excel::create(trans_choice('core.user', 2), function ($excel) {
            $appName = (app()->environment()=='local' ? getenv('APP_NAME') : config('app.name'));

            // Set the title
            $excel->setTitle(trans_choice('core.user', 2));

            // Chain the setters
            $excel->setCreator($appName)
                ->setCompany($appName);

            // Call them separately
            $excel->setDescription('A list of users');
            $excel->sheet(trans_choice('core.user', 2), function ($sheet) {
                //TODO Here we should join grade, role, country to print name not FK
                $users = User::all();
//                $users = User::with(['grade', 'role'])->get();
                $sheet->fromArray($users);
            });


        })->export('xls');
    }
    public function getMyTournaments(Request $request)
    {
        $tournaments = Auth::user()->myTournaments()
            ->orderBy('created_at', 'desc')
            ->paginate(Config::get('constants.PAGINATION'));;

        $title = trans('core.tournaments_registered');
        
        return view('users.tournaments', compact('tournaments','title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return Response
     * @throws \Exception
     * @internal param int $id
     */
    public function destroy(User $user)
    {

        if ($user->delete()) {
            return Response::json(['msg' => trans('msg.user_delete_successful'), 'status' => 'success']);
        } else {
            return Response::json(['msg' => trans('msg.user_delete_error'), 'status' => 'error']);
        }
    }

    public function restore($userSlug)

    {
        $user = User::withTrashed()->whereSlug($userSlug)->first();
        if ($user->restore()) {
            return Response::json(['msg' => trans('msg.user_restore_successful'), 'status' => 'success']);
        } else {
            return Response::json(['msg' => trans('msg.user_restore_successful'), 'status' => 'error']);
        }
    }


    public function uploadAvatar(Request $request){
        $data = $request->except('_token');
        $data = User::uploadPic($data);
        return $data;

    }

}
