<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImage', 'getPostByCategory', 'getPostByUser']]);
    }

    public function index() {

        $posts = Post::all()->load('category');

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'posts' => $posts
                        ], 200);
    }

    public function show($id) {
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Post obtenido.',
                'post' => $post
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No existe el Post.'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //Recogemos los datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //Conseguir usuario identificado
            $user = $this->getIndentity($request);
            //Validamos los datos 
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);
            if ($validate->fails()) {
                //La validacion a fallado
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El Post no se ha guardado',
                );
            } else {
                //Guardamos el post
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'El Post se ha guardado',
                    'category' => $post
                );
            }
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviando ningun dato.Intente otra vez'
            );
        }
        //Devolver resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        //Recojer los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        //Datos para devolver
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Te faltan mas datos para actualizar el post.Intente otra vez'
        );
        if (!empty($params_array)) {
            //Validamos los datos 
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required'
            ]);
            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }
            //Eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            //Conseguir usuario identificado
            $user = $this->getIndentity($request);

            //Conseguir el registro     
            $post = Post::where('id', $id)->where('user_id', $user->sub)->first();
            if (!empty($post) && is_object($post)) {

                //Actualizar el registro  
                $post->update($params_array);
                //Deolver algo
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'El post se ha actualizado con exito.',
                    'post' => $post,
                    'changes' => $params_array
                );
            }           
        }
        //Devolver los datos  
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {
        //Conseguir usuario identificado
        $user = $this->getIndentity($request);

        //Comprobar si existe el registro
        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();
        if (!empty($post)) {
            //Borrarlo
            $post->delete();
            //Devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No existe el post'
            ];
        }
        return response()->json($data, $data['code']);
    }
    
    public function upload(Request $request) {
        //Recoger la imagen de la peticion 
        $image = $request->file('file0');
        //Validar imagen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|mimes:jpg,jpeg,png,gif'
        ]);
        //Guardar la imagen
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se pudo subir la imagen'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();

            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'message' => 'La imagen se ha guardado con exito.',
                'image' => $image_name
            ];
        }
        //Devolver datos
        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        //Comprobar si extiste el fichero
        $isset = \Storage::disk('images')->exists($filename);
        //Conseguir la imagen
        if ($isset) {
            $file = \Storage::disk('images')->get($filename);
            //Devolver la imagen
            return new Response($file, 200);
        } else {
            //Mostrar el error
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }
    
    public function getPostByCategory($id) {
        $post = Post::where('category_id', $id)->get();
        
        return response()->json([
            'status' => 'success',
            'posts' => $post
        ],200);
    }
    
    public function getPostByUser($id) {
        $post = Post::where('user_id', $id)->get();
        
        return response()->json([
            'status' => 'success',
            'posts' => $post
        ],200);
    }

    private function getIndentity($request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }                

}
