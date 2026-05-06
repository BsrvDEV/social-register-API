<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\UserRole;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    //household registration
    public function registerHousehold(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|string|max:15|unique:users',
                'lga_id' => 'required|exists:lgas,id',
                'ward' => 'required|string|max:255',
                'community' => 'required|string|max:255',
                'house_address' => 'required|string|max:255',
                'housing_condition' => 'required|string|max:255',
                'primary_income_source' => 'required|string|max:255',
                'household_size' => 'required|string|max:255',
                'estimated_monthly_income' => 'required|numeric|min:0',
                'male_members' => 'required|numeric|min:0',
                'female_members' => 'required|numeric|min:0',
                'children_count' => 'required|numeric|min:0',
                'elderly_count' => 'required|numeric|min:0',
                'nin' => 'required|numeric|min:0',
                'password' => ['required', 'string', 'min:6', 'confirmed'],
                'password_confirmation' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return respond(false, $validator->errors(), null, 400);
            }

            $existingUser = User::where('email', $request->email)->orWhere('phone', $request->phone)->first();
            if ($existingUser) {
                return respond(false, 'A user with the provided email or phone number already exists.', null, 400);
            }


            DB::transaction(function () use ($request, &$data) {

                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'nin' => $request->nin,
                    'registration_type' => 'household',
                    'password' => Hash::make($request->password),
                    'is_active' => true
                ]);

                $reference = generateHouseholdReference();

                $household = Household::create([
                    'user_id' => $user->id,
                    'application_reference' => $reference,
                    'lga_id' => $request->lga_id,
                    'ward' => $request->ward,
                    'community' => $request->community,
                    'house_address' => $request->house_address,
                    'housing_condition' => $request->housing_condition,
                    'household_size' => $request->household_size,
                    'estimated_monthly_income' => $request->estimated_monthly_income,
                    'primary_income_source' => $request->primary_income_source,
                    'male_members' => $request->male_members,
                    'female_members' => $request->female_members,
                    'children_count' => $request->children_count,
                    'elderly_count' => $request->elderly_count
                ]);

                $data['user'] = $user;
                $data['household'] = $household;

                $loggeduser = Auth::user();
                // $user = User::where('id', $loggeduser->id)->first();
                $userAgent = $request->header('User-Agent');
                $oldValues = "null";
                $description = $user->name . " " . "registered as a household with reference number " . $reference;
                // dd($description);
                $auditableId = $household->id;
                $newValues = $household->getAttributes();
                audit('register_household', Household::class, $user->id, $oldValues, $newValues, $description, $userAgent, $auditableId);
            });

            DB::commit();
            return respond(true, "Household registered successfully", $data, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            log_error($e, [
                'action' => 'Register Household',
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);
            return respond(false, "Error processing request. Please try again.", null, 500);
        }
    }
    public function fetchHousehold(Request $request)
    {

        try{
            $household = Household::all();

            return respond(true, "Household fetched succesfully", $household, 200);
        } catch (\Exception $e){
            return respond(false, "Error fetching household:"  .$e->getMessage(), null, 500);
        }
    }
    public function fetchuserHousehold(Request $request)
    {
        try{
            $user = User::with('household')->where('registration_type', 'household')->get();

            return respond(true, "Household fetched succesfully", $user, 200);
        } catch (\Exception $e){
            return respond(false, "Error fetching household:"  .$e->getMessage(), null, 500);
        }
    }
    public function updateHousehold(Request $request)
    {
        try{
             $validator = Validator::make($request->all(), [
                'id'=>'required|exists:households,id',
                'user_id' => 'nullable|exists:users,id',
                'lga_id' => 'nullable|exists:lgas,id',
                'ward' => 'nullable|string|max:255',
                'community' => 'nullable|string|max:255',
                'house_address' => 'nullable|string|max:255',
                'housing_condition' => 'nullable|string|max:255',
                'primary_income_source' => 'nullable|string|max:255',
                'household_size' => 'nullable|string|max:255',
                'estimated_monthly_income' => 'nullable|numeric|min:0',
                'male_members' => 'nullable|numeric|min:0',
                'female_members' => 'nullable|numeric|min:0',
                'children_count' => 'nullable|numeric|min:0',
                'elderly_count' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return respond(false, $validator->errors(), null, 400);
            }

            $id = $request->id;
            $household = Household::where('id', $id)->first();
            $oldValues = $household->getOriginal();

            $household->update([
                'user_id' => $request->user_id,
                'lga_id' => $request->lga_id,
                'ward' => $request->ward_id,
                'community' => $request->community_id ,
                'house_address' => $request->house_address ,
                'housing_condition' => $request->housing_condition_id ,
                'household_size' => $request->household_size ,
                'estimated_monthly_income' => $request->estimated_monthly_income ,
                'primary_income_source' => $request->primary_income_source ,
                'male_members' => $request->male_members ,
                'female_members' => $request->female_members ,
                'children_count' => $request->children_count ,
                'elderly_count' => $request->elderly_count
            ]);

            $userAgent = $request->header('User-Agent');
            $description = " updated household information";
            $auditableId = $household->id;
            $newValues = $household->getAttributes();

            audit('update_household', Household::class, $household->user_id, $oldValues, $newValues, $description, $userAgent, $auditableId);


            return respond(true, "Household updated successfully", $household, 200);
        }catch (\Exception $e){
            log_error($e, [
                'action' => 'Update Household',
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);
            return respond(false, "Error updating household", null, 500);
        }
    }

    public function fetchHouseholdMembers(Request $request)
    {
        $user = auth()->user();

        if ($user->registration_type !== "household") {
            return respond(false, "Unauthorized", null, 403);
        }

        $household = Household::where('user_id', $user->id)->first();

        if (!$household) {
            return respond(false, "Household not found", null, 404);
        }

        // Optional: pagination
        // $perPage = $request->get('per_page', 15);
        $members = HouseholdMember::where('household_id', $household->id)
                    ->get();

        return respond(true, "Household members retrieved successfully", $members, 200);
    }

    public function addHouseholdMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'relationship' => 'required|string',
            'marital_status' => 'required|string',
            'education_level' => 'nullable|string',
            'occupation' => 'nullable|string',
            'disability' => 'required|in:0,1',
            'chronic_illness' => 'required_if:disability,1',
        ]);

        if ($validator->fails()) {
            return respond(false, $validator->errors(), null, 400);
        }

        $user = auth()->user();

        if ($user->registration_type !== "household") {
            return respond(false, "Unauthorized", null, 403);
        }

        $household = Household::where('user_id', $user->id)->first();

        if (!$household) {
            return respond(false, "Household not found", null, 404);
        }

        try {
            DB::beginTransaction();
            $data = $request->all();

            $data['disability'] = isset($data['disability']) ?? 0;

            $data['household_id'] = $household->id;

            $member = HouseholdMember::create($data);

            $userAgent = $request->header('User-Agent');
            $oldValues = null;
            $newValues = $member->getAttributes();

            $description = $user->name . " added a new household member (" . $member->first_name . " " . $member->last_name . ")";

            audit('add_household_member',HouseholdMember::class, $user->id, $oldValues, $newValues, $description, $userAgent, $member->id);

            DB::commit();
            return respond(true, "Household member added successfully", $member, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            log_error($e, [
                'action' => 'Add Household Member',
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);
            // return respond(false, "Error processing request. Please try again.", null, 500);
            return respond(false, $e->getMessage(), null, 500);
        }
    }

    public function updateHouseholdMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:household_members,id',
            'first_name' => 'sometimes|required|string',
            'last_name' => 'sometimes|required|string',
            'middle_name' => 'sometimes|required|string',
            'date_of_birth' => 'sometimes|required|date',
            'gender' => 'sometimes|required|string',
            'relationship' => 'sometimes|required|string',
            'marital_status' => 'sometimes|required|string',
            'education_level' => 'nullable|string',
            'occupation' => 'nullable|string',
            'disability' => 'sometimes|required|in:0,1',
            'chronic_illness' => 'required_if:disability,1',
        ]);

        if ($validator->fails()) {
            return respond(false, $validator->errors(), null, 400);
        }

        $user = auth()->user();

        if ($user->registration_type !== "household") {
            return respond(false, "Unauthorized", null, 403);
        }

        $household = Household::where('user_id', $user->id)->first();

        if (!$household) {
            return respond(false, "Household not found", null, 404);
        }

        $member = HouseholdMember::where('id', $request->id)
                    ->where('household_id', $household->id)
                    ->first();

        if (!$member) {
            return respond(false, "Household member not found", null, 404);
        }

        try {
            DB::beginTransaction();

            $data = $request->all();

            if ($request->has('disability')) {
                $data['disability'] = $request->disability ? 1 : 0;
            }
            $oldValues = $member->getAttributes();
            $member->update($data);
            $newValues = $member->fresh()->getAttributes();

            $userAgent = $request->header('User-Agent');
            $description = $user->name . " updated household member (" . $member->first_name . " " . $member->last_name . ")";

            audit('update_household_member', HouseholdMember::class, $user->id, $oldValues, $newValues, $description, $userAgent, $member->id);

            DB::commit();

            return respond(true, "Household member updated successfully", $member, 200);

        } catch (\Exception $e) {
            DB::rollBack();
            log_error($e, [
                'action' => 'Update Household Member',
                'member_id' => $request->id,
                'input' => $request->all(),
                'user_id' => Auth::id()
            ]);
            return respond(false, $e->getMessage(), null, 500);
        }
    }

    public function deleteHouseholdMember(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:household_members,id',
        ]);

        if ($validator->fails()) {
            return respond(false, $validator->errors(), null, 400);
        }
        $id = $request->id;
        // dd($request->all()); // <-- add this line

        $user = auth()->user();

        if ($user->registration_type !== "household") {
            return respond(false, "Unauthorized", null, 403);
        }

        $household = Household::where('user_id', $user->id)->first();

        if (!$household) {
            return respond(false, "Household not found", null, 404);
        }

        $member = HouseholdMember::where('id', $id)
                    ->where('household_id', $household->id)
                    ->first();

        if (!$member) {
            return respond(false, "Household member not found", null, 404);
        }

        try {
            DB::beginTransaction();

            $oldValues = $member->getAttributes();
            $member->delete();
            // $member->forceDelete();     // hard delete

            $userAgent = $request->header('User-Agent');
            $description = $user->name . " deleted household member (" . $member->first_name . " " . $member->last_name . ")";

            audit('delete_household_member', HouseholdMember::class, $user->id, $oldValues, null, $description, $userAgent, $id);

            DB::commit();

            return respond(true, "Household member deleted successfully", null, 200);

        } catch (\Exception $e) {
            DB::rollBack();
            log_error($e, [
                'action' => 'Delete Household Member',
                'member_id' => $id,
                'user_id' => Auth::id()
            ]);
            return respond(false, $e->getMessage(), null, 500);
        }
    }

}
