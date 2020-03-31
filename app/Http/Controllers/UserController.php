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
        $request->validate([
            'new_user.name'     =>'required|unique:users,name',
            'new_user.email'    => 'required|unique:users,email',
            'new_user.phone1'   => 'required|unique:users,phone1',
            'new_user.company'  => 'required',
            'new_user.password' => 'required'
        ],
        [
            'new_user.name.unique' => 'Этот логин занят',
            'new_user.email.unique' => 'Эта почта занята',
            'new_user.phone1.unique' => 'Этот номер телефона занят',
            'new_user.name.required' => 'Логин объязателен',
            'new_user.email.required' => 'Почта объязателна',
            'new_user.phone1.required' => 'Номер телефона объязателен',
        ]);

        $tempUser = $request->new_user;

        unset($tempUser['repeatPassword']);

        $user = User::firstOrNew($tempUser);
        $user->password = Hash::make($tempUser['password']);
        $user->api_token = Str::random(60);
        $user->assignRole('client');
        $user->save();

        if ($request->user->can('profile edit additional') && is_array($request->pricing) && count($request->pricing) > 0) {
            $savingData = [];
            foreach ($request->pricing as $key => $pricing) {
                $savingData[$pricing['id']] = ['price' => $pricing['price']];
            }
            $user->plates()->sync($savingData);
        }
        
        if ($user) {
            return response()->json([
                'status' => 'success',
                'message' => 'Пользователь создан'
            ]);
        }
        else {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }
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

        if ($request->user->hasRole('client')) {
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'message' => 'Пользователь найден',
            ]);
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
        unset($tempUser['pricing']);
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
            $passwordChanged = true;
        }

        if ($request->user->can('profile edit additional') && is_array($request->pricing) && count($request->pricing) > 0) {
            $savingData = [];
            foreach ($request->pricing as $key => $pricing) {
                $savingData[$pricing['id']] = ['price' => $pricing['price']];
            }
            $user->rank = $tempUser['rank'];
            $user->trust = $tempUser['trust'];
            $user->comment = $tempUser['comment'];
            $user->plates()->sync($savingData);
        }
        
        $user->save();

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
