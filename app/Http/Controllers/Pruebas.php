<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;


class Pruebas extends Controller
{
    public function testOrm() {
        
        $posts = Post::all();
        //var_dump($post);
        /**foreach ($posts as $post)
        {
            echo "<h1>".$post->title."</h1>";
            echo "<span style='color:red'>{$post->user->name}</span>";
            echo "\n";
            echo "<span style='color:blue'>{$post->user->surname} - {$post->category->name}</span>";
            echo "<p>".$post->content."</p>";
            echo "<hr>";
        }**/
        
        $categories = Category::all();
        foreach ($categories as $category)
        {
            echo "<h1>{$category->name}</h1>";
            
            foreach ($category->posts as $post)
            {
                    echo "<h2>".$post->title."</h2>";
                    echo "<span style='color:red'>{$post->user->name}</span>";
                    echo "\n";
                    echo "<span style='color:blue'>{$post->user->surname} - {$post->category->name}</span>";
                    echo "<p>".$post->content."</p>";            
                    echo "<hr>";
            }        
        }
    die();
    }
}
