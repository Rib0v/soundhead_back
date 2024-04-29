<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    /**
     * Контроллер понадобится в админке
     * для добавления и удаления фото товаров
     */

    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Photo $photo)
    {
        //
    }

    public function update(Request $request, Photo $photo)
    {
        //
    }

    public function destroy(Photo $photo)
    {
        //
    }
}
