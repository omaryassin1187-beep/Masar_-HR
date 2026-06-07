<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileController extends Controller
{
    
    public function index()
    {
        $profile = Auth::user()->profile;
        
        return new ProfileResource($profile);
    }

    
   public function store(StoreProfileRequest $request)
    {
         $validated = $request->validated();
         $userId=Auth::user()->id;
         $profileData = [
             'birth_date' => Carbon::createFromFormat('d-m-Y',$validated['birth_date'])->format('Y-m-d'),
             'gender' => $validated['gender'],
             'phone_number' => $validated['phone_number'],
             'address' => $validated['address'],
             'user_id' =>  $userId,
             'hiring_date' => now()->format('Y-m-d')    //تجلب قيمته من تاريخ توقيع عقد العمل
         ];

         if ($request->hasFile('picture')) {
             $picturePath = $request->file('picture')->store('profile_pictures', 'public');
             $profileData['picture'] = $picturePath;
         }
        
         $profile = Profile::create($profileData);

         return response()->json([
             'message' => 'Profile created successfully',
             'profile' => new ProfileResource($profile)
             
         ], 201);
    }

   
    public function show(string $id)
    {
       $profile=Profile::findOrFail($id);
       return new ProfileResource($profile);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request, string $id)
    {
           $userId = Auth::user()->id;
           $profile=Profile::findOrFail($id);
            if ($profile->user_id != $userId) {
            return response()->json('Unauthenticated.', 403);
        }
           $profile->update($request->validated());
           return new ProfileResource($profile);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
