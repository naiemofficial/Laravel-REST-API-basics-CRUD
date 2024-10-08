<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('Student.index', [
            'model' => new Student,
            'students' => Student::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return 'There\'s no form';
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $student = $request->validate([
                'name'      => 'required',
                'uid'       => ['required', 'unique:students,uid'],
                'email'     => ['email', 'nullable'],
                'phone'     => ['numeric', 'nullable'],
                'address'   => ['string', 'nullable'],
            ]);
            Student::create($student);
            return response()->json('Student created', 201);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'uid' => ['required', 'numeric', 'exists:students,uid'], 
                'name' => ['nullable', 'string', 'min:1', 'filled'], 
                'email' => ['email', 'nullable'],
                'phone' => ['numeric', 'nullable'],
            ]);

            // uid shouldn't be updated
            $uid = $validatedData['uid'];
            unset($validatedData['uid']);

            // Update the student record
            Student::where('uid', $uid)->update($validatedData); // Use the extracted uid for the where clause

            return response()->json('Student updated', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'uid' => ['required', 'numeric', 'exists:students,uid'], 
            ]);

            // Delete the student record
            if (filter_var($request->delete, FILTER_VALIDATE_BOOLEAN)) {
                if(Student::onlyTrashed()->where('uid', $validatedData['uid'])->exists()){
                    Student::where('uid', $validatedData['uid'])->forceDelete();
                    return response()->json('Student permanently deleted', 200);
                } else {
                    return response()->json('Student not found in trash', 404);
                }
            } else {
                Student::where('uid', $validatedData['uid'])->delete();
                return response()->json('Student soft deleted', 200);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }




    /**
     * Restore the specified resource from storage.
     */
    public function restore(Request $request){
        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'uid' => ['required', 'numeric', 'exists:students,uid'], 
            ]);

            // Restore the student record
            if(Student::onlyTrashed()->where('uid', $validatedData['uid'])->exists()){
                Student::onlyTrashed()->where('uid', $validatedData['uid'])->restore();
                return response()->json('Student restored', 200);
            } else {
                return response()->json('Student not found in trash', 404);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }
}
