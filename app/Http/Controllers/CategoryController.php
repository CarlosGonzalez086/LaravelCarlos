<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth',['except' => ['index','show']]);
    }
    
    public function index() {
        $categories = Category::all();
        
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }
    
    public function show($id) {
        $category = Category::find($id);
        
        if(is_object($category)){
             $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Categoria obtenida.',
                'category' => $category
            );
        }else{
             $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No existe la categoria.'                
            );
        }
        return response()->json($data, $data['code']);
    }
    
    public function store(Request $request) {
        //Recoger los datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        if (!empty($params_array)) {
            //Validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha'
            ]);
            //Guardar la categoria
            if ($validate->fails()) {
                //La validacion a fallado
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'La categoria no se ha guardado',                    
                );
            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'La categoria se ha guardado',
                    'category' => $category
                );
            }
        }else{
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
        //Recoger los datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        if (!empty($params_array)) {

            //Validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
            ]);
            //Quitar lo que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);
            //Actualizar el registro(Categoria)
            $category = Category::where('id', $id)->update($params_array);
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'La categoria se ha actualizado con exito.',
                'category' => $category
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviando ningun dato de la categorya selecionada para actualizar.Intente otra vez'
            );
        }
        //Devolver respuesta
        return response()->json($data, $data['code']);
    }

}
