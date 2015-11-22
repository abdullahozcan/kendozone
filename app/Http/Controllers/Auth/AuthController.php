<?php

namespace App\Http\Controllers\Auth;

use App\AuthenticateUser;
use App\Grade;
use App\Mailers\AppMailer;
use App\Role;
use App\User;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Webpatser\Countries\Countries;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;



    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    private $redirectTo = '/';

    public function __construct(Socialite $socialite)
    {

        $this->socialite = $socialite;
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|min:6'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'roleId' => $data['roleId'],
            'countryId' => $data['countryId']

        ]);
    }

    public function getRegister()
    {
        $role = Role::where('name', '=', "Admin")->firstOrFail();
        $roleId = $role->id;
//        dd($roleObj);
        $countries = Countries::lists('name', 'id');
        $grades = Grade::orderBy('order')->lists('name', 'id');
        return view('auth.register', compact('countries', 'grades', 'roleId'));


    }

    /**
     * Perform the registration.
     *
     * @param  Request $request
     * @param  AppMailer $mailer
     * @return \Redirect
     */
    public function postRegister(Request $request, AppMailer $mailer)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users|confirmed',
            'password' => 'required|confirmed'
        ]);
        $user = User::create($request->all());
        $mailer->sendEmailConfirmationTo($user);
        flash('success', Lang::get('auth.check_your_email'));
        return redirect()->back();
    }

    /**
     * Confirm a user's email address.
     *
     * @param  string $token
     * @return mixed
     */
    public function confirmEmail($token)
    {
        User::whereToken($token)->firstOrFail()->confirmEmail();
        flash('You are now confirmed. Please login.');
        return redirect('auth/login');
    }

    /**
     * Attempt to sign in the user.
     *
     * @param  Request $request
     * @return boolean
     */
    protected function signIn(Request $request)
    {
        return Auth::attempt($this->getCredentials($request), $request->has('remember'));
    }

    public function login()
    {

    }


    public function getSocialAuth(AuthenticateUser $authenticateUser, Request $request, $provider = null)
    {

        return $authenticateUser->execute($request->all(), $this, $provider);
    }
    public function userHasLoggedIn($user)
    {
        Session::flash('info', 'Welcome, ' . $user->username);
        return redirect('/');
    }
//    public function getSocialAuthCallback($provider=null)
//    {
//        if($user = $this->socialite->with($provider)->user()){
//            dd($user);
//        }else{
//            return 'something went wrong';
//        }
//    }
    /**
     * Get the login credentials and requirements.
     *
     * @param  Request $request
     * @return array
     */
//    protected function getCredentials(Request $request)
//    {
//        return [
//            'email' => $request->input('email'),
//            'password' => $request->input('password'),
//            'verified' => true
//        ];
//    }

}
