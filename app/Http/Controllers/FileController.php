<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $files = File::all();

        if (is_null($files) || empty($files) || sizeof($files) < 1) {
            return response()->json([
                "message" => "Files List Not Found!"
            ], 401);
        }

        return response()->json([
            "message" => "List of Files Found!",
            "data" => $files
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'file' => 'required|file|mimes:' . File::getAllExtensions() . '|max:' . File::getMaxSize(),
        ]);

        if($validator->fails()){
            return \response()->json([
                "message" => "Validation error",
                "error" => $validator->errors()
            ], 400);
        }

        $file = new File();

        $uploaded_file = $request->file('file');
        $original_ext = $uploaded_file->getClientOriginalExtension();
        $type = $file->getType($original_ext);

        if ($t=$file->upload($type, $uploaded_file, $request['name'], $original_ext)) {
            return $file::create([
                    'name' => $request['name'],
                    'type' => $type,
                    'extension' => $original_ext,
                    'path'=>$t
                ]);
        }

        return response()->json([
            "message" => "File Successfully Created!",
            "data" => $file
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $file = File::get($id);

        if (is_null($file) || empty($file) || sizeof($file) < 1) {
            return response()->json([
                "message" => "File By ID Not Found!"
            ], 401);
        }

        return response()->json([
            "message" => "File by ID Found!",
            "data" => $file
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $file = File::where('id', $id)->first();

        if (is_null($file) || empty($file)) {
            return response()->json([
                "message" => "File by Id not found!"
            ], 401);
        }

        $this->validate($request, [
            'name' => 'required|unique:files'
        ]);

        /**
         * We obtain the old file
         */

        $old_filename = $file->getName(
            $file->type,
            $file->name,
            $file->extension
        );

        /**
         * We replace the contents of the old file
         * with the new $request content
         */

        $new_filename = $file->getName(
            $request['type'],
            $request['name'],
            $request['extension']
        );

        if (Storage::disk('local')->exists($old_filename)) {
            if (Storage::disk('local')->move($old_filename, $new_filename)) {
                $file->name = $request['name'];
                return response()->json($file->save());
            }
        }

        return response()->json([
            "message" => "File Successfully Updated!",
            "data" => $file
        ]);
    }
}
