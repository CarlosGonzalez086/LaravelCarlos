<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//Cargando clases
use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test-orm','Pruebas@testOrm');
//Rutas del API
        /*
         * GET: CONEGUIR DATOS O RECURSOS
         * POST: GUARDAR DATOS O RECURSOS O HACER LOGICA DESDE UN FORMULARIO
         * PUT:ACTUALIZAR DATOS O RECURSOS
         * DELETE:ELIMINAR DATOS O RECURSOS
         */

        //Rutas de pruebas
        //Route::get('pruebasUser','UserController@pruebasuser');
        //Route::get('pruebasCategory','CategoryController@pruebascategory');
        //Route::get('pruebasPost','PostController@pruebaspost');

//Rutas del controlador de usuarios
        Route::post('/api/register','UserController@register');
        Route::post('/api/login','UserController@login');
        Route::put('/api/user/update','UserController@update');
        Route::post('/api/user/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
        Route::get('/api/user/avatar/{filename}','UserController@getImage');
        Route::get('/api/user/detail/{id}','UserController@detail');
        
//Rutas del controlador de categorias
        Route::resource('/api/category', 'CategoryController');

//Rutas del controlador de post   
        Route::resource('/api/post', 'PostController');
        Route::post('/api/post/upload','PostController@upload');
        Route::get('/api/post/image/{filename}','PostController@getImage');
        Route::get('/api/post/user/{id}','PostController@getPostByUser');
        Route::get('/api/post/category/{id}','PostController@getPostByCategory');
        


