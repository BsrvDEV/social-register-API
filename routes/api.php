<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/unauthorized', function () {
    return response()->json(['message' => "Unauthorized"], 403);
})->name('api.unauthorized');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/forgotpassword', [App\Http\Controllers\Api\AuthController::class, 'forgotpassword']);
Route::post('resetpassword', [App\Http\Controllers\Api\AuthController::class, 'resetpassword']);
Route::get('/validate_nin', [App\Http\Controllers\Api\NinVerificationController::class, 'validateNIN']);


Route::post('/register-household', [App\Http\Controllers\Api\RegistrationController::class, 'registerHousehold']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/create_admin', [App\Http\Controllers\Api\AuthController::class, 'CreateAdminUser']);

    Route::get('/fetch_user_household', [App\Http\Controllers\Api\RegistrationController::class, 'fetchuserHousehold']);

    Route::prefix('household')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\RegistrationController::class, 'fetchHousehold']);
        Route::post('/update', [App\Http\Controllers\Api\RegistrationController::class, 'updateHousehold']);
        });

        Route::prefix('household-member')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\RegistrationController::class, 'fetchHouseholdMembers']);
            Route::post('/add', [App\Http\Controllers\Api\RegistrationController::class, 'addHouseholdMember']);
            Route::post('/update', [App\Http\Controllers\Api\RegistrationController::class, 'updateHouseholdMember']);
            Route::get('/delete', [App\Http\Controllers\Api\RegistrationController::class, 'deleteHouseholdMember']);
            });

            Route::post('/applyForAssistance', [App\Http\Controllers\Api\ApplicationController::class, 'applyForAssistance']);
            Route::get('/fetchAssistanceApplication', [App\Http\Controllers\Api\ApplicationController::class, 'fetchAssistanceApplication']);
            Route::get('/fetchAssistanceApplicationbyUser', [App\Http\Controllers\Api\ApplicationController::class, 'fetchAssistanceApplicationbyUser']);
            Route::get('/fetchAllAppliedProgram', [App\Http\Controllers\Api\ApplicationController::class, 'fetchAllAppliedProgram']);

        Route::post('/changePassword', [App\Http\Controllers\Api\AuthController::class, 'changePassword']);
});

