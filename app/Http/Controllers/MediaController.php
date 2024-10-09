<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{   
    /**
     * Necessary funtions
     */
    private $dir = 'content';
    private $media_categories = [
        'images'        => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
        'documents'     => ['doc', 'docx', 'xls', 'xlsx', 'pdf'],
        'compressed'    => ['zip', 'tar', 'gz', '7z'],
        'videos'        => ['mp4', 'mkv', 'avi', 'mov'],
    ];

    public function get_categorized_path($extension){
        $categorized_path = "content/uncategorized";
        foreach ($this->media_categories as $category => $extensions) {
            if (in_array($extension, $extensions)) {
                $categorized_path = "/{$this->dir}/{$category}/";
                break;
            }
        }
        return $categorized_path;
    }

    private function get_destination_path($extension){
        return public_path($this->get_categorized_path($extension));
    }


    public function media_url($file){
        if(is_numeric($file)){
            $media = Media::find($file);
            if (!$media) return null;
            $file = $media->name;
        }
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        return asset(rtrim($this->get_categorized_path($extension), "/").'/'.$file);
    }


    private function file_rename($filename){
        // Check if filename exists then rename by number
        if(!Media::where('name', $filename)->exists()){
            return $filename;
        }
        
        
        $filename_withoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $file_extension      = pathinfo($filename, PATHINFO_EXTENSION);

        $counter = 1;
        $new_filename = $filename_withoutExt.' ('.$counter.').'.$file_extension;
        while(Media::where('name', $new_filename)->exists()){
            $counter++;
            $new_filename = $filename_withoutExt.' ('.$counter.').'.$file_extension;
        }
        return $new_filename;
    }
    private function delete_file_from_storage($path, $filename){
        if(isset($filename) && file_exists(public_path($path . $filename))) {
            unlink(public_path($path . $filename));
        }
    }

    private function delete_media($file)    {
        $media = null;
        if(is_numeric($file)){
            $media = Media::withTrashed()->find($file);
        } else if(is_string($file)){
            $media = Media::withTrashed()->where('name', $file)->first();
        }

        if($media){
            try {
                $extension = pathinfo($media->name, PATHINFO_EXTENSION);
                $file_path = $this->get_destination_path($extension).'/'.$media->name;
                if(file_exists($file_path) && unlink($file_path)){
                    $media->forceDelete();
                    return response()->json(['message' => 'Media has been deleted'], 200);
                } else {
                    return response()->json(['error' => 'Media path not found'], 404);
                }
            } catch (\Exception $e) {
                \Log::error('Error deleting media: ' . $e->getMessage());
                return response()->json(['error' => 'Error deleting media: ' . $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Media instance not found'], 404);
        }
    }
    // ---------------- END - Necessary Functions






    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('Media.index', [
            'model' => new Media,
            'medias' => Media::all(),
            'media_url' => [$this, 'media_url']
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $file = $request->file;
        $filename = $this->file_rename($file->getClientOriginalName());
        $destination_path = rtrim($this->get_destination_path(pathinfo($filename, PATHINFO_EXTENSION)), "/");
        
        
        try {
            $request->validate([
                'file' => 'required|image|mimes:webp,png,jpg,jpeg,svg|max:2048'
            ]);

            $uploaded = $file->move($destination_path, $filename);
            if($uploaded){
                $uploaded_file = new Media;
                $uploaded_file->name = $filename;
                $uploaded_file->save();
                return response()->json(['status' => true, 'message' => 'File uploaded successfully.'], 201);
            } else {
                return respnse()->json(['error' => 'File upload failed.'], 400);
            }
        } catch (ValidationException $e){
            $this->delete_file_from_storage($destination_path, $filename);
            return response()->json($e->errors(), 422);
        } catch (\Exception $e){
            $this->delete_file_from_storage($destination_path, $filename);
            return respnse()->json(['error' => 'Something went wrong.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Media $media)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Media $media)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {   
        $media = Media::find($id);
        if($media){
            try {
                $validatedData = $request->validate([
                    'name'  => ['string', 'min:1', 'required', 'filled'], 
                ]);
                
                $old_filename = $media->name; 
                $extension = pathinfo($old_filename, PATHINFO_EXTENSION);
                $new_filename = $validatedData['name'] . '.' . $extension;
                
                
                if (Media::where('id', '!=', $id)->where('name', $new_filename)->exists()) {
                    return response()->json(['error' => 'File name already exists'], 409);
                }
        
                
                $destination_path = rtrim($this->get_destination_path($extension), "/");
                $old_destination = "{$destination_path}/{$old_filename}";
                $new_destination = "{$destination_path}/{$new_filename}";
                
                
                if (file_exists($old_destination)) {
                    if($old_filename == $new_filename){
                        return response()->json(['info' => 'Old & new filename is same'], 409);
                    } else {
                        if (rename($old_destination, $new_destination)) {
                            $media->name = $new_filename;
                            $media->save();
                            return response()->json(['message' => 'Media updated successfully'], 200);
                        } else {
                            return response()->json(['error' => 'Failed to rename file'], 500);
                        }
                    }
                }
            } catch (ValidationException $e){
                return response()->json($e->errors(), 422);
            } catch (\Exception $e){
                return response()->json(['error' => 'Something went wrong.', 'details' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'File does not exist'], 404); // Handle the case where the file does not exist
        }

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $media = Media::withTrashed()->find($id);
        if($media){
            try {
                // Delete the media
                if (filter_var($request->delete, FILTER_VALIDATE_BOOLEAN)) {
                    if(Media::onlyTrashed()->where('id', $id)->exists()){
                        return $this->delete_media($id);
                    } else {
                        return response()->json('Media not found in trash', 404);
                    }
                } else {
                    Media::where('id', $id)->delete();
                    return response()->json('Media soft deleted', 200);
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json($e->errors(), 422);
            } catch (\Exception $e) {
                \Log::error('Error updating media: ' . $e->getMessage());
                return response()->json(['error' => 'Something went wrong.'], 500);
            }
        } else {
            return response()->json(['error' => 'File does not exist'], 404); // Handle the case where the file does not exist
        }
    }




    /**
     * Restore the specified resource from storage.
     */
    public function restore(Request $request, $id){
        try {    
            // Restore the media
            if(Media::onlyTrashed()->where('id', $id)->exists()){
                Media::onlyTrashed()->where('id', $id)->restore();
                return response()->json('Media restored', 200);
            } else {
                return response()->json('Media not found in trash', 404);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }
}
