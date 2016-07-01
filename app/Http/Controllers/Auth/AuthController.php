<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

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
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */

    /*
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }*/

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

    /**
     * Crea un usuario y verifica antes que no se repitan 
     * los datos de email, cédula y placa de carro.
     *
     * @param  array  $data
     * @return User
     */
    protected function register(Request $request)
    {
        /*
         *  Tipos de Respuestas
         *  1 -> Registro Exitoso
         *  0 -> Registro Fallo, Email ya registrado
         *
        */

        //Consultamos si existe el email

        $consult = new User;

        $consult = DB::table('users')
                    ->select('email')
                    ->where('email', strtolower($consult = $request ->get('email')))
                    ->first();

        if(!$consult){
            //No existe el email, se puede registrar

            //Registramos el nuevo usuario

            $user = new User;

            $user->name = $request->get('name');
            $user->email = strtolower($request->get('email'));
            $user->password = bcrypt($request->get('password'));
            $user->save();

            return response()
                        ->json([
                            'register_status' => 1, 
                            'register_message' => '¡Registro exitoso!',
                            'user' => $user_info
                        ]);
        } else {
            return response()
                    ->json([
                        'register_status' => 0, 
                        'register_message' => 'El Email ya se encuentra registrado'
                    ]);
        }
}
