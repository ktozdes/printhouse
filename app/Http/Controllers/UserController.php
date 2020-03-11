<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\User;
use App\Plate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function list(Request $request)
    {
        $users = User::role('client')->get(['id', 'name', 'fullname', 'company', 'phone1', 'balance', 'rank', 'balance']);
        
        return response()->json([
            'status' => 'success',
            'users' => $users
        ]);
        //return response()->json('plates' =>$plates]);
    }

    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, User $user)
    {
        $permissions = [];
        foreach ($request->user->getAllPermissions() as $key => $permission) {
            $permissions[] = $permission->name;
        }
        return response()->json(['status' => 'success', 'user' =>$request->user, 'permissions' => $permissions]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Plate  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, User $user)
    {
        $request->validate([
            'id'=>'required|integer',
        ]);
        $user = User::where([
            ['id', '=', $request->id],
        ])->first();
        $user->pricing;

        if ($request->user->hasRole('client') && $user['id'] != $request->user->id) {
            return response()->json(['status' => 'error', 'message' => 'У вас недостаточно прав для изменения'], 403);
        }

        if (empty($user)) {
            return response()->json(['status' => 'error', 'message' => 'Этого пользователя нельзя изменить или ее нет'], 400);
        }

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'plates' => Plate::all(['id', 'name', 'price']),
            'message' => 'Пользователь найден',
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'change_user.company'=>'required',
            'change_user.phone1'=>'required',
        ]);
        $tempUser = $request->change_user;
        if ( $request->user->hasRole('client')){
            if ($tempUser['id'] != $request->user->id) {
                return response()->json(['status' => 'error', 'message' => 'У вас недостаточно прав для изменения'], 403);
            }
            unset($tempUser['balance']);
            unset($tempUser['rank']);
            unset($tempUser['trust']);
        }
        unset($tempUser['repeatPassword']);
        
        $phoneEmailCheckUser = DB::table('users')
        ->where('id', '<>', $tempUser['id'])
        ->where(function ($query)  use ($tempUser) {
            $query->where('phone1', '=', $tempUser['phone1']);
            if ( isset($tempUser['email']) ) {
                $query->orWhere('email', '=', $tempUser['email']);
            }
        })
        ->get();

        if (count($phoneEmailCheckUser) > 0) {
            return response()->json(['status' => 'error', 'message' => 'Этот телефонный номер или электронная почта заняты'], 401);
        }

        $user = User::find($tempUser['id']);

        
        $passwordChanged = false;
        $user->update($tempUser);
        if (isset($tempUser['password']) && strlen($tempUser['password']) > 3) {
            $user->password = Hash::make($tempUser['password']);
            $user->api_token = Str::random(60);
            $user->save();
            $passwordChanged = true;
        }

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'passwordChanged' => $passwordChanged,
            'message' => 'Пользователь изменен',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
