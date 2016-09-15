<?php

namespace Backpack\CRUD;

use DB;
use Illuminate\Database\Eloquent\Model;

trait CrudTrait
{
    /*
    |--------------------------------------------------------------------------
    | Methods for ENUM and SELECT crud fields.
    |--------------------------------------------------------------------------
    */

    public static function getPossibleEnumValues($field_name)
    {
        $instance = new static(); // create an instance of the model to be able to get the table name
        $type = DB::select(DB::raw('SHOW COLUMNS FROM '.$instance->getTable().' WHERE Field = "'.$field_name.'"'))[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = [];
        foreach (explode(',', $matches[1]) as $value) {
            $enum[] = trim($value, "'");
        }

        return $enum;
    }

    public static function isColumnNullable($column_name)
    {
        $instance = new static(); // create an instance of the model to be able to get the table name
        $answer = DB::select(DB::raw("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='".$instance->getTable()."' AND COLUMN_NAME='".$column_name."' AND table_schema='".env('DB_DATABASE')."'"))[0];

        return $answer->IS_NULLABLE === 'YES';
    }

    /*
    |--------------------------------------------------------------------------
    | Methods for Fake Fields functionality (used in PageManager).
    |--------------------------------------------------------------------------
    */

    /**
     * Add fake fields as regular attributes, even though they are stored as JSON.
     *
     * @param array $columns - the database columns that contain the JSONs
     */
    public function addFakes($columns = ['extras'])
    {
        foreach ($columns as $key => $column) {
            $column_contents = $this->{$column};

            if (! is_object($this->{$column})) {
                $column_contents = json_decode($this->{$column});
            }

            if (count($column_contents)) {
                foreach ($column_contents as $fake_field_name => $fake_field_value) {
                    $this->setAttribute($fake_field_name, $fake_field_value);
                }
            }
        }
    }

    /**
     * Return the entity with fake fields as attributes.
     *
     * @param array $columns - the database columns that contain the JSONs
     *
     * @return Model
     */
    public function withFakes($columns = [])
    {
        $model = '\\'.get_class($this);

        if (! count($columns)) {
            $columns = (property_exists($model, 'fakeColumns')) ? $this->fakeColumns : ['extras'];
        }

        $this->addFakes($columns);

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Methods for storing uploaded files (used in CRUD).
    |--------------------------------------------------------------------------
    */

    /**
     * Handle file upload and DB storage for a file:
     * - on CREATE
     *     - stores the file at the destination path
     *     - generates a name
     *     - stores the full path in the DB;
     * - on UPDATE
     *     - if the value is null, deletes the file and sets null in the DB
     *     - if the value is different, stores the different file and updates DB value.
     *
     * @param  [type] $value            Value for that column sent from the input.
     * @param  [type] $attribute_name   Model attribute name (and column in the db).
     * @param  [type] $disk             Filesystem disk used to store files.
     * @param  [type] $destination_path Path in disk where to store the files.
     */
    public function uploadFileToDisk($value, $attribute_name, $disk, $destination_path)
    {
        $request = \Request::instance();

        // if a new file is uploaded, delete the file from the disk
        if ($request->hasFile($attribute_name) &&
            $this->{$attribute_name} &&
            $this->{$attribute_name} != null) {
            \Storage::disk($disk)->delete($this->{$attribute_name});
            $this->attributes[$attribute_name] = null;
        }

        // if the file input is empty, delete the file from the disk
        if (is_null($value) && $this->{$attribute_name} != null) {
            \Storage::disk($disk)->delete($this->{$attribute_name});
            $this->attributes[$attribute_name] = null;
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if ($request->hasFile($attribute_name) && $request->file($attribute_name)->isValid()) {

            // 1. Generate a new file name
            $file = $request->file($attribute_name);
            $new_file_name = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();

            // 2. Move the new file to the correct path
            $file_path = $file->storeAs($destination_path, $new_file_name, $disk);

            // 3. Save the complete path to the database
            $this->attributes[$attribute_name] = $file_path;
        }
    }

    /**
     * Handle multiple file upload and DB storage:
     * - if files are sent
     *     - stores the files at the destination path
     *     - generates random names
     *     - stores the full path in the DB, as JSON array;
     * - if a hidden input is sent to clear one or more files
     *     - deletes the file
     *     - removes that file from the DB.
     *
     * @param  [type] $value            Value for that column sent from the input.
     * @param  [type] $attribute_name   Model attribute name (and column in the db).
     * @param  [type] $disk             Filesystem disk used to store files.
     * @param  [type] $destination_path Path in disk where to store the files.
     */
    public function uploadMultipleFilesToDisk($value, $attribute_name, $disk, $destination_path)
    {
        $request = \Request::instance();
        $attribute_value = (array) $this->{$attribute_name};
        $files_to_clear = $request->get('clear_'.$attribute_name);

        // if a file has been marked for removal,
        // delete it from the disk and from the db
        if ($files_to_clear) {
            $attribute_value = (array) $this->{$attribute_name};
            foreach ($files_to_clear as $key => $filename) {
                \Storage::disk($disk)->delete($filename);
                $attribute_value = array_where($attribute_value, function ($value, $key) use ($filename) {
                    return $value != $filename;
                });
            }
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if ($request->hasFile($attribute_name)) {
            foreach ($request->file($attribute_name) as $file) {
                if ($file->isValid()) {
                    // 1. Generate a new file name
                    $new_file_name = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();

                    // 2. Move the new file to the correct path
                    $file_path = $file->storeAs($destination_path, $new_file_name, $disk);

                    // 3. Add the public path to the database
                    $attribute_value[] = $file_path;
                }
            }
        }

        $this->attributes[$attribute_name] = json_encode($attribute_value);
    }

    /**
     * Handles the retrieval of an image by variant:
     *
     * @param  [type] $attribute        Name of the attribute within the model that contains the json
     * @param  [type] $variant          Name of the variant you want to extract
     * @param  [type] $disk             Filesystem disk used to store files.
     */
    public function getUploadedImageFromDisk($attribute, $variant = 'original', $disk = null)
    {
        $image = $this->attributes['image'];
        $url = null;

        if( !empty($image) ){

            if( !is_array($image) ){
                $image = json_decode($image);
            }

            if( $disk ){
                $url = \Storage::disk($disk)->url(trim($image->{$variant}, '/'));
            }
            else {
                $url = url($image->{$variant});
            }
        }
        return $url;
    }

    /**
     * Handle image upload and DB storage for a image:
     * - on CREATE
     *     - stores the image at the destination path
     *     - generates a name
     *     - creates image variations
     *     - stores json object into database with variations and paths
     * - on UPDATE
     *     - if the value is null, deletes the file and sets null in the DB
     *     - if the value is different, stores the different file and updates DB value.
     *
     * @param  [type] $value            Value for that column sent from the input.
     * @param  [type] $attribute_name   Model attribute name (and column in the db).
     * @param  [type] $disk             Filesystem disk used to store files.
     * @param  [type] $destination_path Path in disk where to store the files.
     * @param  [type] $variations       Array of variations and their dimensions
     */
    public function uploadImageToDisk($value, $attribute_name, $disk, $destination_path, $variations = null)
    {
        if (!$variations || !is_array($variations)) {
            $variations = ['original' => null, 'thumb' => [150,150]];
        }

        //Needed for the original image
        if( !array_key_exists('original', $variations) ){
            $variations['original'] = null;
        }

        //Needed for admin thumbnails
        if( !array_key_exists('thumb', $variations) ){
            $variations['thumb'] = [150,150];
        }

        $request = \Request::instance();

        //We need to setup the disk paths as they're handled differently
        //depending if you need a public path or internal storage
        $disk_config = config('filesystems.disks.'.$disk);
        $disk_root = $disk_config['root'];

        //if the disk is public, we need to know the public path
        if( $disk_config['visibility'] == 'public' ){
            $public_path = str_replace(public_path(), '', $disk_root);
        }
        else {
            $public_path = $disk_root;
        }

        // if a new file is uploaded, delete the file from the disk
        if ($request->hasFile($attribute_name) &&
            $this->{$attribute_name} &&
            is_array($this->{$attribute_name})) {
            foreach($this->{$attribute_name} as $variant){
                \Storage::disk($disk)->delete($variant);
            }
            $this->attributes[$attribute_name] = null;
        }

        // if the file input is empty, delete the file from the disk
        if (empty($value) && is_array($this->{$attribute_name})) {
            foreach($this->{$attribute_name} as $variant){
                \Storage::disk($disk)->delete($variant);
            }
            return $this->attributes[$attribute_name] = null;
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if ($request->hasFile($attribute_name) && $request->file($attribute_name)->isValid()) {

            // 1. Generate a new file name
            $file = $request->file($attribute_name);
            $new_file_name = md5($file->getClientOriginalName().time());
            $new_file = $new_file_name.'.'.$file->getClientOriginalExtension();

            // 2. Move the new file to the correct path
            $file_path = $file->storeAs($destination_path, $new_file, $disk);
            $image_variations = [];

            // 3. but only if they have the ability to crop/handle images
            if(class_exists('\Intervention\Image\ImageManagerStatic')){

                $img = \Intervention\Image\ImageManagerStatic::make($file);

                foreach( $variations as $variant => $dimensions ){

                    $variant_name = $new_file_name.'-'.$variant.'.'.$file->getClientOriginalExtension();
                    $variant_file = $destination_path . '/' . $variant_name;

                    if( $dimensions ){
                        $width  = $dimensions[0];
                        $height = $dimensions[1];

                        if( $img->width() > $width || $img->height() > $height ){
                            $img->resize($width, $height, function($constraint){
                                $constraint->aspectRatio();
                            })
                            ->save($disk_root.'/'.$variant_file);
                        }
                        else {
                            $img->save($disk_root.'/'.$variant_file);
                        }

                        $image_variations[$variant] = $public_path.'/'.$variant_file;
                    }
                    else {
                        $image_variations['original'] = $public_path.'/'.$file_path;
                    }
                }
            }
            else {
                $image_variations['original'] = $public_path.'/'.$file_path;
                $image_variations['thumb'] = $public_path.'/'.$file_path;
            }

            // 3. Save the complete path to the database
            $this->attributes[$attribute_name] = json_encode($image_variations);
        }
    }
}
