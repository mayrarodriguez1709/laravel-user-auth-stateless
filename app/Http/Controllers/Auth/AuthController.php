<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Auth;

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
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    /*
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }*/

     /**
     * Crea un usuario y verifica antes que no se repita el email
     *
     * @param  array  $data
     * @return User
     */

    
    protected function create(Request $request)
    {   
        /*
         *  Tipos de Respuestas
         *  1 -> Registro Exitoso
         *  0 -> Registro Fallo, Email ya registrado
         *
        */

        $email = $request->get('email');

        $user = User::where('email', $email)->first();

        if(!$user){
            User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => bcrypt($request['password']),
            ]);   

            return response()
                        ->json([
                            'status' => 1,
                            'message' => 'Registro exitoso'
                        ]);

        }else{
            return response()
                        ->json([
                            'status' => 0,
                            'status' => 'El Email ya se encuentra registrado'
                        ]);
        }
    }

    /**
     * Verifica que exista el usuario en la BD
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function login(Request $request)
    {   
        /*
         *  Tipos de Respuestas
         *  1 -> El Usuario si existe
         *  0 -> El Usuario no existe
         *
        */

        $credentials = [
            'email' => strtolower($request->get('email')),
            'password' => $request->get('password'),
        ];

        // Permite generar una consulta en stateless
        if (Auth::once($credentials)) {
            
            $user = Auth::user();
            $id = Auth::user()->id;

            //Cambiamos el Token
            $user->remember_token = str_random(100);
            $user->save();

            $user_info = array(
                'email' => $user->email,
                'remember_token' => $user->remember_token); 

            return response()
                    ->json([
                        'login_status' => 1, 
                        'login_message' => 'Bienvenido',
                        'user' => $user_info
                    ]);


        } else {
            return response()
                    ->json([
                        'login_status' => 0, 
                        'login_message' => 'Datos incorrectos'
                    ]);
        }   
    }

}
