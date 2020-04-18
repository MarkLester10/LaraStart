<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;


class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('isAdmin');
        return User::latest()->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('isAdmin');
        $this->validate($request,[
            'name'=>'required|string|min:5',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|string|min:8',
            'type'=>'required',
            'bio'=>'string|max:150',
        ]);

        return User::create([
            'name'=> $request->input('name'),
            'email'=> $request->input('email'),
            'password'=> Hash::make($request->input('password')),
            'type'=> $request->input('type'),
            'bio'=> $request->input('bio'),
            'photo'=> $request->input('photo'),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function profile()
    {
        return auth('api')->user();
    }

    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();
        $this->validate($request,[
            'name'=>'required|string|min:5',
            'email'=>'required|string|email|unique:users,email,'.$user->id,
            'password'=>'sometimes|required|string|min:8',
            'bio'=>'string|max:150',
        ]);

        $currentPhoto = $user->photo;

        if($request->photo != $currentPhoto){
            $extension = explode('/', mime_content_type($request->photo))[1];
            $name = time().'.'.$extension;
            \Image::make($request->photo)->save(public_path('imgs/profile/').$name);
            $request->merge(['photo'=>$name]);

            $oldPhoto = public_path('imgs/profile/'.$user->photo);
            if($user->photo != 'user.png' && file_exists($oldPhoto)){
                @unlink($oldPhoto);
            }
        }

        if(!empty($request->password)){
            $request->merge(['password'=>Hash::make($request->password)]);
        }

        $user->update($request->all());

        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {   
        $this->authorize('isAdmin');
        $user = User::findOrFail($id);
        $data = $this->validate($request,[
            'name'=>'required|string|min:5',
            'email'=>'required|string|email|unique:users,email,'.$user->id,
            'password'=>'sometimes|required|string|min:8',
            'type'=>'sometimes|required',
            'bio'=>'string|max:150',
        ]);

        $user->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('isAdmin');
        $user = User::findOrFail($id);
        // $user->delete();
        $user->delete();

        return ['message'=>'User Deleted'];
    }
}
