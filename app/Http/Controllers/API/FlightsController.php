<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Flights;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Logs;
use Illuminate\Support\Facades\Validator;
use Flash;
use Response;

class FlightsController extends Controller {

    public $successStatus = 200;

    public function login(){
    if (Auth::attempt(['username'=> request('username'), 'password' => request('password')])){
        $user = Auth::user();
      
            $success['token'] = Str::random(64);
            $success['username'] = $user->username;
            $success['id'] = $user->id;
            $success['name'] = $user->name;
    
            // SAVE THE TOKEN
            $user->remember_token = $success['token'];
            $user->save();
      
            $logs = new Logs();
      
            $logs->userid = $user->id;
            $logs->log = "Login";
            $logs['logdetails'] = "User $user->username has logged in into my system";
            $logs['logtype'] = "API login";
            $logs->save();
            return response()->json($success, $this->successStatus);
        } else {
            return response()->json(['response' => 'User Not Found'], 404);
        }        
    }

    public function register(Request $request)
    {
//        validate the users credentials
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['response' => $validator->errors()], 401);
        } else {
            $input = $request->all();

            if (User::where('email', $input['email'])->exists()) {
                return response()->json(['response' => 'Email already exists'], 401);
            } else if(User::where('username', $input['username'])->exists()) {
                return response()->json(['response' => 'Username already exists'], 401);
            } else {
                $input['password'] = bcrypt($input['password']);
                $user = User::create($input);

                $success['token'] = Str::random(64);
                $success['username'] = $user->username;
                $success['id'] = $user->id;
                $success['name'] = $user->name;

                return response()->json($success, $this->successStatus);
            }
        }

    }

    public function resetPassword(Request $request)
    {
        $user = User::where('email',$request['email'])->first();
        if($user != null){
            $user->password = bcrypt($request['password']);
            $user->save();

            return response()->json(['response'=>'User has successfully resetted his/her password'],200);
        }else {
            return response()->json(['response'=>'User not found']);
        }
    }

}

?>