<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\NinVerification;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class NinVerificationController extends Controller
{

    public function validateNIN(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nin' => 'required|digits:11',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $nin = $request->nin;
            $existing = NinVerification::where('nin', $nin)->first();
            if ($existing) {
                return response()->json([
                    'status' => true,
                    'message' => 'NIN already verified.',
                    'source' => $existing->source,
                    'data' => $existing,
                ]);
            }

            try {
                $response = Http::timeout(15)->retry(2, 100)->get(
                    'https://api-olarms.ogunstate.gov.ng/api/check_nin_cac',
                    ['data' => ['nin' => $nin]]
                );

                if ($response->successful()) {
                    $res = $response->json();

                    if (isset($res['status']) && $res['status'] === true) {
                        $ninData = $this->normalizeNinData('olarms_api', $res['data']);
                        $record = NinVerification::create(array_merge($ninData, [
                            'source' => 'olarms_api',
                            'raw_response' => $res,
                        ]));

                        return response()->json([
                            'status' => true,
                            'message' => 'NIN verified successfully (Olarms).',
                            'source' => 'olarms_api',
                            'data' => $record,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // \Log::error('Olarms API error: ' . $e->getMessage());
            }

            try {
                $response = Http::timeout(15)->retry(2, 100)->get(
                    'https://api.ogetax.ogunstate.gov.ng/api/validate-nin-cac',
                    ['data' => ['nin' => $nin]]
                );

                if ($response->successful()) {
                    $res = $response->json();

                    if (isset($res['status']) && $res['status'] === true) {
                        $ninData = $this->normalizeNinData('ogirs_api', $res['data']);
                        $record = NinVerification::create(array_merge($ninData, [
                            'source' => 'ogirs_api',
                            'raw_response' => $res,
                        ]));

                        return response()->json([
                            'status' => true,
                            'message' => 'NIN verified successfully (OGIRS).',
                            'source' => 'ogirs_api',
                            'data' => $record,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // \Log::error('OGIRS API error: ' . $e->getMessage());
            }

            try {
                $response = Http::timeout(15)->retry(2, 100)->get(
                    'https://api.mppud.ogunstate.gov.ng/api/get-nincac-details',
                    ['data' => ['nin' => $nin]]
                );

                if ($response->successful()) {
                    $res = $response->json();

                    if (isset($res['status']) && $res['status'] === true && isset($res['data'])) {
                        $ninData = $this->normalizeNinData('mppud_api', $res['data']);
                        $record = NinVerification::create(array_merge($ninData, [
                            'source' => 'mppud_api',
                            'raw_response' => $res,
                        ]));

                        return response()->json([
                            'status' => true,
                            'message' => 'NIN verified successfully (MPPUD).',
                            'source' => 'mppud_api',
                            'data' => $record,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // \Log::error('MPPUD API error: ' . $e->getMessage());
            }

            try {
                $apiKey = env('MOMO_STKEY');
                $client = new \GuzzleHttp\Client();

                $response = $client->request('POST', 'https://api.withmono.com/v3/lookup/nin', [
                    'body' => json_encode(['nin' => $nin]),
                    'headers' => [
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                        'mono-sec-key' => $apiKey,
                    ],
                ]);

                $body = json_decode($response->getBody(), true);

                if (($body['status'] ?? '') !== 'successful') {
                    return response()->json([
                        'status' => false,
                        'message' => $body['message'] ?? 'Mono verification failed.',
                        'source' => 'mono_api',
                    ], 400);
                }

                $ninData = $this->normalizeNinData('mono_api', $body['data']);
                $record = NinVerification::create(array_merge($ninData, [
                    'source' => 'mono_api',
                    'raw_response' => $body,
                ]));

                return response()->json([
                    'status' => true,
                    'message' => 'NIN verified successfully (Mono).',
                    'source' => 'mono_api',
                    'data' => $record,
                ]);

            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $error = json_decode($e->getResponse()->getBody(), true);
                return response()->json([
                    'status' => false,
                    'message' => $error['message'] ?? 'Mono API error.',
                ], 400);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }
        private function normalizeNinData($source, $data)
    {
        switch ($source) {
            case 'olarms_api': // Olarms
                return [
                    'nin' => $data['nin'] ?? null,
                    'title' => $data['title'] ?? null,
                    'first_name' => $data['first_name'] ?? null,
                    'last_name' => $data['last_name'] ?? null,
                    'middle_name' => $data['middle_name'] ?? null,
                    'full_name' => $data['name'] ?? null,
                    'date_of_birth' => $data['dob'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'alternate_phone' => $data['nin_phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'state_of_origin' => $data['state_of_origin'] ?? null,
                    'state_of_residence' => $data['state'] ?? null,
                    'lga' => $data['lga'] ?? null,
                    'city' => $data['city'] ?? null,
                    'address' => $data['address'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                    'marital_status' => $data['marital_status'] ?? null,
                    'profession' => $data['job_title'] ?? null,
                    'kin_first_name' => $data['kin_first_name'] ?? null,
                    'kin_last_name' => $data['kin_last_name'] ?? null,
                    'kin_phone' => $data['kin_phone'] ?? null,
                    'kin_email' => $data['kin_email'] ?? null,
                    'kin_address' => $data['kin_address'] ?? null,
                    'business_name' => $data['business_name'] ?? null,
                    'job_title' => $data['job_title'] ?? null,
                    'company_email' => $data['company_email'] ?? null,
                    'company_phone' => $data['company_phone'] ?? null,
                    'company_address' => $data['company_address'] ?? null,
                    'rc_number' => $data['rc_number'] ?? null,
                    'photo' => $data['photo'] ?? null,
                ];

            case 'ogirs_api': // OGIRS
                return [
                    'nin' => $data['nin'] ?? null,
                    'first_name' => $data['othername'] ?? null,
                    'last_name' => $data['surname'] ?? null,
                    'middle_name' => null,
                    'full_name' => trim(($data['othername'] ?? '') . ' ' . ($data['surname'] ?? '')),
                    'date_of_birth' => $data['date_of_birth'] ?? $data['dob'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'phone' => $data['phone_number'] ?? null,
                    'email' => $data['email'] ?? null,
                    'address' => $data['office_address'] ?? $data['residential_address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state_of_origin' => null,
                    'state_of_residence' => null,
                    'nationality' => $data['nationality'] ?? null,
                    'photo' => $data['photo'] ?? null,
                    'tax_identification_id' => $data['tax_identification_id'] ?? null,
                    'reference_number' => $data['reference_number'] ?? null,
                    'company_reg_number' => $data['company_reg_number'] ?? null,
                ];

            case 'mppud_api':
                return [
                    'nin' => $data['nin'] ?? null,
                    'title' => $data['title'] ?? null,
                    'first_name' => $data['first_name'] ?? null,
                    'last_name' => $data['last_name'] ?? null,
                    'middle_name' => $data['middle_name'] ?? null,
                    'full_name' => $data['name'] ?? null,
                    'date_of_birth' => $data['dob'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'phone' => $data['phone'] ?? $data['nin_phone'] ?? null,
                    'alternate_phone' => $data['nin_phone'] ?? null,
                    'email' => $data['email'] ?? $data['kin_email'] ?? null,
                    'state_of_origin' => $data['state_of_origin'] ?? null,
                    'state_of_residence' => $data['state'] ?? null,
                    'lga' => $data['lga'] ?? null,
                    'city' => $data['city'] ?? null,
                    'address' => $data['address'] ?? $data['company_address'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                    'marital_status' => $data['marital_status'] ?? null,
                    'profession' => $data['job_title'] ?? null,
                    'kin_first_name' => $data['kin_first_name'] ?? null,
                    'kin_last_name' => $data['kin_last_name'] ?? null,
                    'kin_phone' => $data['kin_phone'] ?? null,
                    'kin_email' => $data['kin_email'] ?? null,
                    'kin_address' => $data['kin_address'] ?? null,
                    'business_name' => $data['business_name'] ?? null,
                    'job_title' => $data['job_title'] ?? null,
                    'company_email' => $data['company_email'] ?? null,
                    'company_phone' => $data['company_phone'] ?? null,
                    'company_address' => $data['company_address'] ?? null,
                    'rc_number' => $data['rc_number'] ?? null,
                    'photo' => $data['photo'] ?? null,
                ];

            case 'mono_api':
                return [
                    'nin' => $data['nin'] ?? null,
                    'title' => $data['title'] ?? null,
                    'first_name' => $data['firstname'] ?? null,
                    'last_name' => $data['surname'] ?? null,
                    'middle_name' => $data['middlename'] ?? null,
                    'full_name' => trim(
                        ($data['firstname'] ?? '') . ' ' .
                        ($data['middlename'] ?? '') . ' ' .
                        ($data['surname'] ?? '')
                    ),
                    'date_of_birth' => isset($data['birthdate'])
                        ? Carbon::createFromFormat('d-m-Y', $data['birthdate'])->format('Y-m-d')
                        : ($data['date_of_birth'] ?? null),
                    'gender' => $data['gender'] ?? null,
                    'phone' => $data['telephoneno'] ?? $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'rc_number' => $data['rc_number'] ?? null,
                    'state_of_origin' => $data['state_of_origin'] ?? null,
                    'state_of_residence' => $data['residence_state'] ?? null,
                    'lga' => $data['residence_lga'] ?? null,
                    'city' => $data['residence_Town'] ?? null,
                    'address' => $data['residence_AdressLine1'] ?? null,
                    'nationality' => $data['birthcountry'] ?? null,
                    'marital_status' => $data['maritalstatus'] ?? null,
                    'profession' => $data['profession'] ?? null,
                    'kin_first_name' => $data['nok_firstname'] ?? null,
                    'kin_last_name' => $data['nok_surname'] ?? null,
                    'kin_address' => $data['nok_address1'] ?? null,
                    'photo' => $data['photo'] ?? null,
                ];

            default:
                return [];
        }
    }

}
