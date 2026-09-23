<?php

// namespace App\Http\Controllers;
namespace Modules\CoreWeb\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\Authentication\Entities\User;

use Illuminate\Support\Facades\Hash;

class DeleteUserController extends Controller
{

    public function deleteAccount(){
        return view('coreweb::user.delete');
    }

   public function checkAndDelete(Request $request)
    {
        $request->validate([
            'mobile_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('mobile', $request->mobile_number)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid password'], 401);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
