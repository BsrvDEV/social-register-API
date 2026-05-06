<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NinVerification;
use App\Models\User;
use App\Models\UserRole;
use App\Models\userResetToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->all();
        $messages = [
            'email.exists' => 'There is no account linked to the provided email.',
        ];
        $validator = Validator::make($data, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return respond(false, $validator->errors(), null, 400);
        }
        try {
            $user = User::where('email', $request->email)->first();

            // Check if the user exists and is locked
            // if ($user->locked_at) {
            //     return respond(false, 'Your account is locked due to too many failed login attempts.', null, 403);
            // }

            if (!$user || !Hash::check($request->password, $user->password)) {
                //increase the number of failed attempt
                // $user->increment('failed_attempts');
                // if ($user->failed_attempts >= 5) {
                //     $user->update(['locked_at' => now()]);
                //     return respond(false, 'Too many failed attempts. Your account is now locked.', null, 429);
                // }
                return respond(false, 'Invalid Credentials', $data, 400);
            }


            // $permissions = $user->permissions->pluck('name')->toArray();

            $token = $user->createToken('myAppToken')->plainTextToken;

            $response = [
                'user' => $user,
                'token' => $token,
                // 'permissions' => $permissions,
            ];

            return respond(true, 'Login Successful', $response, 200);
        } catch (\Exception $e) {
            log_error($e, [
                'action' => 'Customer Logging In',
                'input' => $request->all(),
                'user_id' => $user->id
            ]);
            return respond(false, 'System Busy! Try Again Later', null, 400);
        }
    }

    public function CreateAdminUser(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:1024',
                'last_name' => 'required|string|max:1024',
                'phone' => 'required|unique:users,phone|numeric|regex:/^[0-9]{11}$/|starts_with:0',
                'email' => 'required|email|unique:users,email',
                'role_id' => 'nullable|exists:roles,id',
                // 'permission' => 'nullable|array',
                // 'permission.*' => 'nullable|exists:permissions,name',
            ]);
            if ($validator->fails()) {
                return respond(false, $validator->errors(), null, 400);
            }
            $input = $request->all();
            $input['name'] = $input['first_name'] . " " . $input['last_name'];
            // $input['created_by'] = auth()->user()->id;

            // $password = Str::random(8);//"password";
            $password = "password";
            $input['password'] = Hash::make($password);
            $input['registration_type'] = "admin";
            $input['is_active'] = true;
            // $input['visible'] = 1;
            //$data['password'] = Hash::make($password);

            // if (isset($input['email'])) {

            //     try {
            //         Mail::to($input['email'])->send(new AdminPasswordEmail($password));
            //     } catch (\Exception $mailException) {
            //         log_error($mailException, [
            //             'action' => 'Sending user email',
            //             'input' => $request->all(),
            //             'user_id' => auth()->user()->id,
            //         ]);
            //         // Continue execution even if mail fails
            //     }
            //     // Send email with password
            //     // Mail::to($input['email'])->send(new AdminPasswordEmail($password));
            // }


            // dd('here');

            // if ($request->has('phone')) {
            //     $formattedNumber = formatPhoneNumber($request->phone);
            //     if ($formattedNumber['status'] == false) {
            //         return response()->json([
            //             'status' => 'error',
            //             'message' => $formattedNumber['message'],
            //             'data' => $formattedNumber['data'],
            //         ], 400);
            //     }
            // }

            // if ($request->has('image')) {
            //     $input['image'] = uploadImage($request->image, "image");
            // } else {
            //     $input['image'] = null;
            // }
            $user = User::create($input);

            $role = Role::where('id', $request->role_id)->first();
            if ($request->has('role_id')) {
                $user->assignRole($role->name);
                //     $permissions = $request->input("permission");
                // // $role->syncPermissions($permissions, 'guard');
                //     $role->syncPermissions($permissions);
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $role->id
                ]);

            }
            // if ($request->has('permission')) {

            //     $permissions = $request->input("permission");
            //     $user->syncPermissions($permissions);

            // }
            $date = now();
            $loggeduser = Auth::user();
            // $user = User::where('id', $loggeduser->id)->first();
            $userAgent = $request->header('User-Agent');
            $oldValues = "null";
            $description = $loggeduser->name . " " . "created user ($user->name)";
            // dd($description);
            $auditableId = $user->id;
            $newValues = $user->getAttributes();
            audit('create', User::class, Auth::id(), $oldValues, $newValues, $description, $userAgent, $auditableId);

            DB::commit();
            return respond(true, 'New user created successfully!', $user, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            log_error($e, [
                'action' => 'Create User',
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);
            return respond(false, "Error processing request. Please try again.", null, 500);
        }
    }
    
    public function forgotpassword (Request $request){
        $validator = Validator::make($request->all(),[
            'email'=> 'required|email'
        ]);
        if ($validator->fails()) {
            return respond(false, $validator->errors(), null, 400);
        }
        try{
            $user = User::where('email',$request->email)->first();
            if(!$user){
                return back()->withErrors(['email' =>'No user found with this email']);
            }
            $token = Str::random(60);
            $expires_at = Carbon::now()->addMinutes(60);

            userResetToken::create([
                'email'=> $request->email,
                'type'=> 'Forgot Password',
                'token' => $token,
                'expires_at' => $expires_at
            ]);

            // DB:: table('password_reset_tokens')->insert([
            //     'email'=> $request-> email,
            //     'token' => $token,
            //     'created_at' => now()
            // ]);
            try {
                Mail::to($user->email)->send(new ResetPasswordMail($token, $user->name));
            } catch (\Exception $e) {
                log_error($e, [
                    'action' => 'forgot_password_mail',
                    'input' => $request->all(),
                    'user_id' => Auth::id()
                ]);
            }
            return respond (true, "Email sent succesfully to your inbox", null, 200);
        } catch (\Exception $e) {
            log_error($e, [
                'action' => 'reset-password',
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);
            // return respond(false, "Error processing request. Please try again.", null, 500);
            return respond(false, $e->getMessage(), null, 500);
        }

    }

    public function resetpassword (Request $request){
        $validator = Validator::make($request->all(), [
            'password'=>'required|confirmed|min:6',
            'token'=> 'required'
        ]);
        if ($validator->fails()) {
            return respond(false, $validator->errors(), null, 400);
        }

        try {
            DB::beginTransaction();
            $otp = userResetToken::where([
                ['token',$request->token],
                ['type','Forgot Password'],
            ])->latest()->first();

            // $resetPassword = DB::table('password_reset_tokens')->where('token', $request->token)->latest()->first();
            // if(!$resetPassword){
            //     return respond(false, 'Invalid token',null,404);
            // }

            if (!$otp) {
                return response()->json(['message' => 'Invalid token'], 400);
            }
            // if ($otp->isExpired()) {
            // return response()->json(['message' => 'Token expired, request a new password reset link'], 400);
            // }

            $user = User::where('email', $otp->email)->first();
            $oldValues = $user->getOriginal();
            $user->password = Hash::make($request->password);
            $user->save();

            $date = now();

            // $user = User::where('id', $loggeduser->id)->first();
            $userAgent = $request->header('User-Agent');
            $description = $user->name . " " . "reset password on $date";
            // dd($description);
            $auditableId = $user->id;
            $user->refresh();
            $newValues = $user->getAttributes();
            audit('reset-password', User::class, $user->id, $oldValues, $newValues, $description, $userAgent, $auditableId);
            $otp->delete();
            DB::commit();
            return respond(true, 'Password reset successfully!', $user, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            log_error($e, [
                'action' => 'reset-password',
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);
            // return respond(false, "Error processing request. Please try again.", null, 500);
            return respond(false, $e->getMessage(), null, 500);
        }
    }
    
    public function changePassword(Request $request) {
       try {
            $user = Auth::user();

            if (!$user) {
                return respond(false, 'Unauthenticated', null, 401);
            }

            $validator = Validator::make($request->all(),[
                'old_password' => 'required|string',
                'new_password' => 'required|string|min:8|different:old_password',
            ]);
        
            if ($validator->fails()) {
                return respond(false, $validator->errors(), null, 400);
            }

            if (!Hash::check($request->old_password, $user->password)) {
                return respond(false, 'Old password is incorrect', null, 400);
            }
            
            $user->password = Hash::make($request->new_password);
            $user->save();
            
            DB::commit();
            return respond(true, 'Password changed succesfully', null, 200);
        }catch (\Exception $e ) {
            DB::rollBack();
            log_error($e, [
                'action' => 'change_password',
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);
            return respond(false, $e->getMessage(), null, 500);
        }
    }
}
