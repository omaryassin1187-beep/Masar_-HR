<?php

namespace App\Http\Controllers\Attendance_Leaves;

use Illuminate\Http\Request;
use App\Models\Attendance_Leaves\Holiday;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class HolidayController extends Controller
{
    
    public function index()
    {
        $holidays=Holiday::all();
         return response()->json([
                'message' => ' Our Holidays :',
                'data' => $holidays
                
            ], 200);
    }

    
     public function store(Request $request)
    {
         $request->validate([
           'name'=>'required|string',
           'type'=>'required|in:official,company',
           'date'=>'required|date'
        ]);

        $holiday=Holiday::create([
           'name'=>$request->name,
           'type'=>$request->type,
           'date'=>Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d'),
        ]);

        return response()->json([
                'message' => 'Holiday created successfully',
                'data' => $holiday
                
            ], 201);
    }

    
    public function show(string $id)
    {
        $holiday= Holiday::findOrFail($id);  
        return response()->json($holiday,200);
    }

    
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
           'name'=>'sometimes|string',
           'type'=>'sometimes|in:official,company',
           'date'=>'sometimes|date'
            
        ]);

        $holiday= Holiday::findOrFail($id); 
       
            $holiday->update($validated);
       
        return response()->json([
                'message' => 'Holiday updated successfully',
                'data' => $holiday
                
            ], 200);

    }

    
    public function destroy(string $id)
    {
           $holiday= Holiday::findOrFail($id);
           $holiday->delete();
           return response()->json('deleted done successfully',200);
    }
    
}
