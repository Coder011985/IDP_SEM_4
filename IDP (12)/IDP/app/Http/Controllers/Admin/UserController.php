<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | User List + Filters + Online Stats
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {

        $query = User::query();

        // SEARCH
        if ($request->filled('search')) {

            $query->where(function($q) use ($request){

                $q->where('name','like','%'.$request->search.'%')
                  ->orWhere('email','like','%'.$request->search.'%');

            });

        }

        // ROLE FILTER
        if ($request->filled('role')) {
            $query->where('role',$request->role);
        }


        $users = $query->latest()->paginate(15)->withQueryString();



        $totalUsers = User::count();

        $totalDesigners = User::where('role','designer')->count();

        $totalClients = User::where('role','client')->count();



        $onlineUsers = User::where('last_seen_at','>=',now()->subMinutes(2))->count();

        $offlineUsers = User::where(function($q){

            $q->whereNull('last_seen_at')
              ->orWhere('last_seen_at','<',now()->subMinutes(2));

        })->count();



        return view('admin.users.index',[

            'users' => $users,

            'totalUsers' => $totalUsers,
            'totalDesigners' => $totalDesigners,
            'totalClients' => $totalClients,

            'onlineUsers' => $onlineUsers,
            'offlineUsers' => $offlineUsers

        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | Edit User
    |--------------------------------------------------------------------------
    */

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }



    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, User $user)
    {

        $request->validate([
            'specialization' => 'nullable|in:residential,commercial,office,luxury',
            'status' => 'required|in:active,inactive',
        ]);


        // Protect main admin
        if ($user->role === 'admin' && $user->id === 1) {

            return back()->with('error','Main admin cannot be modified.');

        }


        $user->update([
            'specialization' => $request->specialization,
            'status' => $request->status,
        ]);


        return redirect()
            ->route('admin.users.index')
            ->with('success','User updated successfully.');

    }

}
