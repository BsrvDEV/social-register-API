<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssistanceApplication;
use App\Models\AssistanceApplicationAssignment;
use Illuminate\Support\Facades\Validator;
use App\Models\Household;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ApplicationController extends Controller
{
    public function applyForAssistance(Request $request){
       try{
           DB::beginTransaction();
         $validator = Validator::make($request->all(),[
            'reason'=> 'required|string',
            'household_beneficiary_count'=> 'required|numeric|min:0',
            'program_id'=> 'required|exists:programmes,id',
            'member_id'=> 'required|exists:household_members,id'
        ]);
        if ($validator->fails()) {
            return respond(false, $validator->errors(), null, 400);
        }
        $user = auth()->user();
        $household = Household::where('user_id', $user->id)->first();

        $applicationCode = generateAssistanceApplicationCode();

        $application = AssistanceApplication::create([
            'household_id' => $household->id,
            'reason' => $request->reason,
            'household_beneficiary_count' => $request->household_beneficiary_count,
            'program_id' => $request->program_id,
            'member_id' => $request->member_id,
            'application_code' => $applicationCode
        ]);

        $user = Auth::user();
        $date = now();
        $userAgent = $request->header('User-Agent');
        $loggeduser = Auth::user();
        $description = $loggeduser->name . " " . "applied for assistance on $date";
        $auditableId = $user->id;
        audit('apply for assisatnce', User::class, Auth::id(), $userAgent, $description, $auditableId);

        
        DB::commit();
        return respond(true, "Assistance Appliciation successfull", $application, 200);
       }catch(\Exception $e){
             DB::rollBack();
            log_error($e, [
                'action' => 'Assistance application created succesfully',
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);
            return respond(false, $e->getMessage(), null, 500);
       }
    }

    public function fetchAssistanceApplication(Request $request){
        $assistance_application = AssistanceApplication::with(['household','program','member'])->get();

        return respond(true, "Assistance application fetched successfully", $assistance_application,200);
    }

    public function fetchAssistanceApplicationbyUser(Request $request){
        try{
            $user = auth()->user();

            if ($user->registration_type !== "household") {
                return respond(false, "Unauthorized", null, 403);
            }

            $household = Household::where('user_id', $user->id)->first();

            if (!$household) {
                return respond(false, "Household not found", null, 404);
            }

            $application = AssistanceApplication::with(['program','member'])
                    ->where('household_id', $household->id)
                    ->get();

            return respond(true, "Assistance application fetched successfully", $application, 200);
        } catch (\Exception $e){
            return respond(false, "Error fetching assistance application:"  .$e->getMessage(), null, 500);
        }
    }

    public function fetchAllAppliedProgram (Request $request) {
        try {
            $user = auth()->user();

            if ($user->registration_type !== "household") {
                return respond(false, "Unauthorized", null, 403);
            }

            $household = Household::where('user_id', $user->id)->first();

            if (!$household) {
                return respond(false, "Household not found", null, 404);
            }

            $application = AssistanceApplication::with(['program','member'])
                    ->where('household_id', $household->id)
                    ->get();

            return [
                'metrics' =>[
                    'total'=>$application->count(),
                    'approved'=>$application->where('approval_status',true)->count(),
                    'under_review'=>$application->where('approval_status',false)->count(),
                ],
                'data' =>$application
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            log_error($e, [
                'action' => 'Fetched household application Metrics',
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);
            return respond(false, $e->getMessage(), null, 500);
        }
    }

   public function assignAdmin(Request $request)
    {
        try {
           DB::beginTransaction();

           $validRoles = [
                'Zonal Officer'
            ];

            $validator = Validator::make($request->all(), [
                'application_id' => 'required|exists:assistance_applications,id',
                'admin_id' => [
                    'required',
                    'exists:users,id',
                    function ($attribute, $value, $fail) use ($validRoles) {

                        $user = User::find($value);

                        if (
                            !$user ||
                            !$user->roles()->whereIn('name', $validRoles)->exists()
                        ) {
                            $fail('Selected user must be a Zonal Officer.');
                        }
                    }
                ]
            ]);

            if ($validator->fails()) {
                return respond(false, $validator->errors(), null, 400);
            }
            
            $application = AssistanceApplication::find($request->application_id);

            if (!$application) {
                return respond(false, 'Application not found', null, 404);
            }

            if ($application->assigned_admin) {
                return respond(false, 'Application already assigned', null, 400);
            }

            $admin = User::find($request->admin_id);

            if (!$admin) {
                return respond(false, 'Admin not found', null, 404);
            }

            $roleId = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $admin->id)
            ->where('roles.name', 'Zonal Officer')
            ->value('roles.id');

            AssistanceApplicationAssignment::create([
                'assistance_application_id' => $application->id,
                'applicant_id' => $application->household_id,
                'admin_id' => $admin->id,
                'officer_title_id' => $roleId
            ]);

            $application->update([
                'assigned_admin' => true
            ]);

            DB::commit();

            return respond(true, 'Admin assigned successfully', $application, 200);
        } catch (\Exception $e) {
            DB::rollBack();

            log_error($e, [
                'action' => 'assign_admin',
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);

            return respond(false, $e->getMessage(), null, 500);
            }
    }
}