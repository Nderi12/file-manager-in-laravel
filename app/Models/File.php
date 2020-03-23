<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class File extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'cbs_file_manager';

    protected $guarded = ['creation_date', 'update_date'];

    const CREATED_AT = 'creation_date';

    const UPDATED_AT = 'update_date';

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->log_id = (string) Str::uuid();
            $model->id = DB::select("select nextval('cbs_file_manager_id_seq')")[0]->nextval;
        });
    }

    public function getRouteKeyName()
    {
        return 'log_id';
    }

    protected $fillable = [
        'name',
        'type',
        'extension',
        'path',
        'log_id'
    ];

    public static $image_ext = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
    public static $audio_ext = ['mp3', 'ogg', 'mpga'];
    public static $video_ext = ['mp4', 'mpeg'];
    public static $document_ext = ['doc', 'docx', 'pdf', 'odt'];

    /**
     * Get maximum file size
     * @return int maximum file size in kilobites
     */
    public static function getMaxSize()
    {
        return (int) ini_get('upload_max_filesize') * 10000;
    }

    /**
     * Get directory for the specific user
     * @return string Specific user directory
     */
    public function getUserDir()
    {
        return 'customer';
        // return Auth::user()->name . '_' . Auth::id();
    }

    /**
     * Get all extensions
     * @return array Extensions of all file types
     */
    public static function getAllExtensions()
    {
        $merged_arr = array_merge(self::$image_ext, self::$audio_ext, self::$video_ext, self::$document_ext);
        return implode(',', $merged_arr);
    }

    /**
     * Get type by extension
     * @param  string $ext Specific extension
     * @return string      Type
     */
    public function getType($ext)
    {
        if (in_array($ext, self::$image_ext)) {
            return 'image';
        }

        if (in_array($ext, self::$audio_ext)) {
            return 'audio';
        }

        if (in_array($ext, self::$video_ext)) {
            return 'video';
        }

        if (in_array($ext, self::$document_ext)) {
            return 'document';
        }
    }

    /**
     * Get file name and path to the file
     * @param  string $type      File type
     * @param  string $name      File name
     * @param  string $extension File extension
     * @return string            File name with the path
     */
    public function getName($type, $name, $extension)
    {
        return 'public/' . $this->getUserDir() . '/' . $type . '/' . $name . '.' . $extension;
    }

    /**
     * Upload file to the server
     * @param  string $type      File type
     * @param  object $file      Uploaded file from request
     * @param  string $name      File name
     * @param  string $extension File extension
     * @return string|boolean    String if file successfully uploaded, otherwise - false
     */
    public function upload($type, $file, $name, $extension)
    {
        $path = str_replace('.','_','public/uploads' . '/' . $type);
        $full_name = $name . '_'.str_replace('.','_',microtime()).'.' . $extension;
        $full_name=str_replace(' ','_',$full_name);

        return (string) Storage::putFileAs($path, $file, $full_name);
    }
}
