<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller {

    public function pruebasuser(Request $request) {
        return "Accion de pruebas de USER-CONTROLLER";
    }

    public function register(Request $request) {
        
        //Recoger los datos del usuario por post
        $json = $request->input('json', null);
        //Decodificar los datos
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        //Validacion por medio de un if
        if (!empty($params) && !empty($params_array)) {
            //Limpiar datos
            $params_array = array_map('trim', $params_array);
            //Validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users', //Comprobar si el usuario existe    
                        'password' => 'required'
            ]);
            if ($validate->fails()) {
                //La validacion a fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                //Validacion correctamente
                //Cifrar la contraseña
                $pwd = hash('sha256', $params->password);
                //Creal el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                //Guardar el usuario
                $user->save(); //Realiza un insert into a la base de datos automaticamente
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos no son correctos.Intente otra vez',
            );
        }
        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {
        
        $jwtAuth = new \JwtAuth();
        //Recibbir datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        //Validar esos datos
        $validate = \Validator::make($params_array, [
                    'email' => 'required|email', //Comprobar si el usuario existe    
                    'password' => 'required'
        ]);
        if ($validate->fails()) {
            //La validacion a fallado
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha podido indentificar',
                'errors' => $validate->errors()
            );
        } else {
            //Cifrar la password
            $pwd = hash('sha256', $params->password);
            //Devolver token o datos
            $signup = $jwtAuth->signup($params->email, $pwd);
            if (!empty($params->gettoken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }
        return response()->json($signup, 200);
    }

    public function update(Request $request) {
        
        //Comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
        //Recoger datos por post            
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        if ($checkToken && !empty($params_array)) {
            //Actualizar el usuario                       
            //Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);
            //Validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users' . $user->sub //Comprobar si el usuario existe                     
            ]);
            //Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);
            //Actualizar el usuario en la base de datos
            $user_update = User::where('id', $user->sub)->update($params_array);
            //Devolver array con resultados
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'El usuario se ha actualizado con exito.',
                'user' => $user_update,
                'Usuario Viejo' => $user,
                'Usuario Actualizado' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta indentificado.'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {
        
        //Recoger datos de la peticion
        $image = $request->file('file0');
        //Validar imagen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mines:jpg,jpeg,png,gif'
        ]);
        //Guardar imagen
        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen.'
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }
        //Devolver el resultado               
        return response($data, $data['code'])->header('Content-Type', 'text/plain');
    }

    public function getImage($filename) {
        
        $isset = \Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Esta imagen no existe.'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function detail($id) {
        
        $user = User::find($id);
        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Usuario obtenido.',
                'user' => $user
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Este usuario no existe.'
            );
        }
        return response()->json($data, $data['code']);
    }
}
