<?php
// use Jenssegers\Agent\Facades\Agent;
use App\Models\AdhocUsers;
use App\Models\AdminAddDocument;
use App\Models\AdminNotification;
use App\Models\ApplicationAssessment;
use App\Models\Customer;
use App\Models\CustomerApplication;
use App\Models\CustomerNotification;
use App\Models\DocumentType;
use App\Models\ErrorLog;
use App\Models\GenerateAssessmentBreakdown;
use App\Models\GovernmentSurveyRate;
use App\Models\LandChartingFee;
use App\Models\LandUse;
use App\Models\LocalGovt;
use App\Models\Location;
use App\Models\ModifiedApplicationAssessment;
use App\Models\Payment;
use App\Models\PaymentBreakdown;
use App\Models\Service;
use App\Models\ServiceFee;
use App\Models\SquareMeterSpecification;
use App\Models\SupportLog;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

if (!function_exists('cleanCompanyName')) {

    function cleanCompanyName($name)
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9 ]/', '', $name);
        $name = preg_replace('/\b(inc|incorporated|llc|ltd|limited|corp|corporation)\b/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }
}

function randomDigits()
{
    return Str::random(8);
}
function randomKey()
{
    return Str::random(25);
}


function generateAssistanceApplicationCode()
{
    $year = now()->year;

    $counter = DB::table('reference_counters')
        ->where('type', 'household')
        ->where('year', $year)
        ->lockForUpdate()
        ->first();

    if (!$counter) {
        DB::table('reference_counters')->insert([
            'type' => 'household',
            'year' => $year,
            'current_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $number = 1;
    } else {
        $number = $counter->current_number + 1;

        DB::table('reference_counters')
            ->where('id', $counter->id)
            ->update([
                'current_number' => $number,
                'updated_at' => now()
            ]);
    }

    $sequence = str_pad($number, 5, '0', STR_PAD_LEFT);

    return "OG-APP-{$year}-{$sequence}";
}
function generateHouseholdReference()
{
    $year = now()->year;

    $counter = DB::table('reference_counters')
        ->where('type', 'household')
        ->where('year', $year)
        ->lockForUpdate()
        ->first();

    if (!$counter) {
        DB::table('reference_counters')->insert([
            'type' => 'household',
            'year' => $year,
            'current_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $number = 1;
    } else {
        $number = $counter->current_number + 1;

        DB::table('reference_counters')
            ->where('id', $counter->id)
            ->update([
                'current_number' => $number,
                'updated_at' => now()
            ]);
    }

    $sequence = str_pad($number, 5, '0', STR_PAD_LEFT);

    return "OG-HH-{$year}-{$sequence}";
}
function isJsonEncoded($string)
{
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

function isBase64Encoded($string)
{
    return base64_encode(base64_decode($string, true)) === $string;
}
function random5()
{

    return Str::random(10000, 99999);

}

function convertToDate($period)
{
    return Carbon::createFromFormat('M Y', substr($period, 0, 3) . ' ' . substr($period, 3));
}
function audit($action, $modelType, $modelId, $oldValues = [], $newValues = [], $description = null, $agents = null, $auditable = null)
{
    $agent = new Agent();
    // Get device information
    $deviceName = $agent->device();
    // $deviceName = $device['device'];
    // Get operating system information
    $platform = $agent->platform();
    // Get browser information
    $browser = $agent->browser();
    $userAgent = $agent->getUserAgent();
    // dd($userAgent);
    //   $deviceName = Agent::device();
    //   $platform = Agent::platform();
    //   $browser = Agent::browser();
    $userId = Auth::id() ?? $modelId;
    // $name = Auth::user()->first_name ?? "" . ' '. Auth::user()->last_name ?? "";
    DB::table('audit_trails')->insert([
        'user_id' => $userId,
        'action' => $action,
        'description' => $description,
        'model_type' => $modelType,
        'url' => url()->current(),
        'machine_name' => $deviceName . ' , ' . $platform . ' , ' . $browser . ' ' . $userAgent,
        'ip_address' => request()->ip(),
        'model_id' => $modelId,
        'auditable_id' => $auditable,
        'old_values' => json_encode($oldValues),
        'new_values' => json_encode($newValues),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function formatPhoneNumber($phoneNumber)
{
    $nigeriaPrefixes = [
        '070',
        '080',
        '081',
        '090',
        '0700',
        '0802',
        '0803',
        '0804',
        '0805',
        '0806',
        '0807',
        '0808',
        '0809',
        '0810',
        '0811',
        '0812',
        '0813',
        '0814',
        '0815',
        '0816',
        '0817',
        '0818',
        '0819',
        '0902',
        '0903',
        '0904',
        '0905',
        '0906',
        '0907',
        '0908',
        '0909',
        '07025',
        '07026',
        '07027',
        '07028',
        '07029',
        '01',
        '02',
        '03',
        '04',
        '05',
        '06',
        '07',
        '09'
    ];

    $moroccoPrefixes = ['05', '06', '07'];
    $usaPrefixes = ['+1'];
    $ukPrefixes = ['+44'];

    // Remove any non-digit characters
    $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

    // Validate Nigerian phone numbers with proper lengths
    if (strlen($phoneNumber) === 11) {
        foreach ($nigeriaPrefixes as $prefix) {
            if (substr($phoneNumber, 0, strlen($prefix)) === $prefix) {
                return [
                    'status' => true,
                    'message' => 'Valid Nigerian phone number',
                    'formatted' => '+234' . substr($phoneNumber, 1),
                ];
            }
        }
    } elseif (strlen($phoneNumber) === 13 && strpos($phoneNumber, '234') === 0) {
        return [
            'status' => true,
            'message' => 'Valid Nigerian phone number',
            'formatted' => '+' . $phoneNumber,
        ];
    } elseif (strlen($phoneNumber) === 12 && strpos($phoneNumber, '212') === 0) {
        return [
            'status' => true,
            'message' => 'Valid Moroccan phone number',
            'formatted' => '+' . $phoneNumber,
        ];
    } elseif (strlen($phoneNumber) === 11 && strpos($phoneNumber, '1') === 0) {
        return [
            'status' => true,
            'message' => 'Valid USA phone number',
            'formatted' => '+' . $phoneNumber,
        ];
    } elseif (strlen($phoneNumber) === 12 && strpos($phoneNumber, '44') === 0) {
        return [
            'status' => true,
            'message' => 'Valid UK phone number',
            'formatted' => '+' . $phoneNumber,
        ];
    }

    return [
        'status' => false,
        'message' => 'Invalid phone number format',
        'formatted' => $phoneNumber,
    ];
}




function success_status_code()
{
    return 200;
}

function bad_response_status_code()
{
    return 400;
}
function api_request_response($status, $message, $statusCode, $data = [], $return = false, $returnArray = false)
{
    $responseArray = [
        "status" => $status,
        "message" => $message,
        "data" => $data
    ];

    $response = response()->json(
        $responseArray
    );

    if ($returnArray) {
        return $returnArray;
    }

    if ($return) {
        return $response;
    }

    header('Content-Type: application/json', true, $statusCode);

    echo json_encode($response->getOriginalContent());

    exit();
}

function signature()
{
    $api_key = "d62f3d28-158a-4393-9148-625618e9992d"; // Your Signature API Key
    // $api_key = "a3902a3b-a519-4af8-bbe5-db7dd0276cf9"; // Your Signature API Key
    // $api_key = "bcee3f27-6e06-4f77-a435-330cba24ef1a"; // Your Signature API Key
    $partner_id = "7271"; // Your partner ID
    // $timestamp = time();
    // Generate timestamp in required ISO format with milliseconds
    // $timestamp = Carbon::now('UTC')->format("Y-m-d\TH:i:s.vK");
    $timestamp = Carbon::now('UTC')->format("Y-m-d\TH:i:s.v\Z");
    // $timestamp = Carbon::now('UTC')->format("Y-m-d\TH:i:s.vP");
    // Construct the message as per API requirements
    $message = $timestamp . $partner_id . 1;

    // Generate HMAC SHA256 signature and encode it in Base64
    $signature = base64_encode(hash_hmac('sha256', $message, $api_key, true));

    return [$signature, $timestamp];
}

function generate_uuid()
{
    return \Ramsey\Uuid\Uuid::uuid1()->toString();
}


function hasConsecutiveDuplicates($array)
{
    $length = count($array);

    for ($i = 0; $i < $length - 1; $i++) {
        if ($array[$i] === $array[$i + 1]) {
            return true; // Consecutive duplicates found
        }
    }

    return false; // No consecutive duplicates found
}
function convertToUppercase($word)
{
    $words = explode(' ', $word);
    $result = '';
    foreach ($words as $word) {
        $result .= strtoupper(substr($word, 0, 1));
    }
    return $result;
    // return response()->json(['converted_word' => $result]);
}

function respond($status, $message, $data, $code)
{
    return response()->json([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ], $code);
}

function responding($status, $message, $data, $dataone, $code)
{
    return response()->json([
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'data_one' => $dataone,
    ], $code);
}

function uploadImage($file, $path)
{
    if ($file == null) {
        return null;
    }
    if ($file->getSize() > 2 * 1024 * 1024) {
        return 'The image size must not exceed 2MB.';
    }
    $image_name = $file->getClientOriginalName();
    $image_name_withoutextensions = pathinfo($image_name, PATHINFO_FILENAME);
    $name = str_replace(" ", "", $image_name_withoutextensions);
    $image_extension = $file->getClientOriginalExtension();
    $file_name_extension = trim($name . '.' . $image_extension);
    $uploadedFile = $file->move(public_path($path), $file_name_extension);
    return url('/') . '/' . $path . '/' . $file_name_extension;
}



function getYearRange($startYear, $endYear)
{
    $years = [];
    for ($year = $startYear; $year <= $endYear; $year++) {
        $years[] = $year;
    }
    return $years;
}


function CustomerNotifications($userId, $message, $roleID, $appID)
{
    // dd($item,$old,$new,$quantity,$stock,$amount);
    $notification = new CustomerNotification();
    $notification->user_id = $userId;
    $notification->message = $message;
    $notification->role_id = $roleID;
    // $notification->amount = $cumMessage;
    $notification->application_id = $appID;
    $notification->save();
}

function AdminNotification($userId, $message, $roleID, $appNo, $appType, $appID)
{
    // dd($item,$old,$new,$quantity,$stock,$amount);
    $notification = new AdminNotification();
    $notification->user_id = $userId;
    $notification->message = $message;
    $notification->role_id = $roleID;
    $notification->application_number = $appNo;
    $notification->app_type = $appType;
    // $notification->amount = $cumMessage;
    $notification->application_id = $appID;
    $notification->save();
}


// function generateFIleNoForRat()
// {
//     $year = date('Y'); // Current year (2025)
//     $prefix = 'OGSG/RAT/WDE/'; // Your prefix

//     do {
//         // Generate a random number (e.g., between 1000 and 9999)
//         $randomNumber = rand(1000, 9999);
//         $fileNumber = $prefix . $randomNumber . '/' . $year;

//         // Check if this number already exists
//         $exists = CustomerApplication::where('file_number', $fileNumber)->exists();
//     } while ($exists); // Keep generating until we find a unique number

//     return $fileNumber;
// }

function generateOgNumber($type)
{
    $year = date('Ymd'); // Current year (2025)
    $randomNumber = rand(1000, 9999);
    // Define prefixes based on application type
    $prefixes = "OG/$year/$randomNumber";

    return $prefixes;
}

function generate_token()
{
    $client = new Client();
    $key = "francismogbana@initsng.com";//env('QOREID_API_KEY');
    $secret = "password";//env('QOREID_SECRET_KEY');
    $baseUrl = env('EDMSAPIURL');
    try {
        $response = $client->request('POST', "$baseUrl/auth/login", [
            'body' => '{"email":"' . $key . '","password":"' . $secret . '"}',
            'headers' => [
                'accept' => 'text/plain',
                'content-type' => 'application/json',
            ],
        ]);

        $response = json_decode($response->getBody(), true);
        // dd($response);
        return [
            'token' => $response['access_token'],
        ];

    } catch (\Exception $e) {
        return [
            'message' => 'Something went wrong with generating token from EDMS',
            'error' => $e->getMessage(),
        ];
    }

}

function uploadImageThirdParty($type, $file, $fileNumber, $survey, $token)
{
    $client = new Client();
    $baseUrl = env('EDMSAPIURL');
    // dd($token);
    // /folder/:applicationID/documents/add

    $application_id = $fileNumber;
    $id = auth()->user()->company_id;
    // dd('here');
    // $response = $client->post("$baseUrl/folder/1/documents/add", [
    $response = $client->post("$baseUrl/documents/add", [
        'multipart' => [
            // Other payloads
            [
                'name' => 'document_type',
                'contents' => $type,
            ],
            [
                'name' => 'applicant_id',
                'contents' => auth()->user()->company_id,
            ],
            [
                'name' => 'applicant_name',
                'contents' => auth()->user()->name,
            ],
            [
                'name' => 'application_id',
                'contents' => $fileNumber,
            ],
            [
                'name' => 'survey_number',
                'contents' => $survey,
            ],
            [
                'name' => 'file_number',
                'contents' => $fileNumber,
            ],
            // File payload
            [
                'name' => 'document', // Change to required key name
                'contents' => fopen($file->getPathname(), 'r'),
                'filename' => $file->getClientOriginalName(),
                'headers' => [
                    'Content-Type' => $file->getMimeType(),
                ],
            ],
        ],
        'headers' => [
            'Authorization' => "Bearer  $token",
        ]
    ]);
    $response = json_decode($response->getBody(), true);
    // dd($response);
    return $response['data']['file_url'];
    // return response()->json([
    //     'status' => $response->getStatusCode(),
    //     'body' => json_decode($response->getBody(), true),
    // ]);
}

if (!function_exists('log_error')) {
    function log_error($e, array $context = []): ?ErrorLog
    {
        try {
            // Get simplified stack trace (3 frames max)
            $trace = collect($e->getTrace())->take(3)->map(function ($frame) {
                return [
                    'file' => str_replace(base_path(), '', $frame['file'] ?? ''),
                    'line' => $frame['line'] ?? null,
                    'action' => ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '')
                ];
            })->all();

            // Prepare clean context data
            $cleanContext = [
                'action' => $context['action'] ?? 'unknown',
                'input' => request()->except(['password', 'token', 'credit_card']),
                'user_id' => Auth::id(),
                'ip' => request()->ip()
            ];

            // Create the error log with properly encoded JSON
            return ErrorLog::create([
                'level' => 'error',
                'message' => class_basename($e) . ': ' . Str::limit($e->getMessage(), 200),
                'exception' => get_class($e),
                // 'stack_trace' => json_encode($trace, JSON_UNESCAPED_SLASHES),
                'context' => json_encode($cleanContext, JSON_UNESCAPED_SLASHES),
                'url' => parse_url(request()->fullUrl(), PHP_URL_PATH) ?: '/',
                'method' => request()->method(),
                'user_id' => Auth::id(),
                'user_agent' => request()->header('User-Agent'),
                'ip_address' => request()->ip(), // Make sure this is included
                'created_at' => now()
            ]);

        } catch (\Throwable $loggingError) {
            Log::error('Failed to log error', [
                'original_error' => $e->getMessage(),
                'logging_error' => $loggingError->getMessage()
            ]);
            return null;
        }
    }
}
if (!function_exists('log_error')) {
    function log_errorEDMS($e, array $context = []): ?ErrorLog
    {
        try {
            // Get simplified stack trace (3 frames max)
            $trace = collect($e->getTrace())->take(3)->map(function ($frame) {
                return [
                    'file' => str_replace(base_path(), '', $frame['file'] ?? ''),
                    'line' => $frame['line'] ?? null,
                    'action' => ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '')
                ];
            })->all();

            // Prepare clean context data
            $cleanContext = [
                'action' => $context['action'] ?? 'unknown',
                'input' => request()->except(['password', 'token', 'credit_card']),
                'user_id' => Auth::id(),
                'ip' => request()->ip()
            ];

            // Create the error log with properly encoded JSON
            return ErrorLog::create([
                'level' => 'error',
                'message' => class_basename($e) ?? NULL . ': ' . Str::limit($e->getMessage() ?? 'No message', 200),
                'exception' => get_class($e),
                // 'stack_trace' => json_encode($trace, JSON_UNESCAPED_SLASHES),
                'context' => json_encode($cleanContext, JSON_UNESCAPED_SLASHES),
                'url' => parse_url(request()->fullUrl(), PHP_URL_PATH) ?: '/',
                'method' => request()->method(),
                'user_id' => Auth::id(),
                'user_agent' => request()->header('User-Agent'),
                'ip_address' => request()->ip(), // Make sure this is included
                'created_at' => now()
            ]);

        } catch (\Throwable $loggingError) {
            Log::error('Failed to log error', [
                'original_error' => $e->getMessage(),
                'logging_error' => $loggingError->getMessage()
            ]);
            return null;
        }
    }
}

function tryParseDate(string $input): ?DateTime
{
    $formats = [
        'Y-m-d',    // 2023-12-31
        'm/d/Y',    // 12/31/2023
        'd.m.Y',    // 31.12.2023
        'Ymd',      // 20231231
        'Y-m-d H:i',   // Date with time
        'Y-m-d H:i:s'  // Date with time and seconds
    ];

    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $input);
        if ($date && $date->format($format) === $input) {
            return $date;
        }
    }

    return null;
}

function uploadToThirdParty($files, $applicantId, $applicationId, $token, $name)
{
    try {
        $request = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ]);
        $envUrl = env('EDMS_API_URL');
        // $envToken = env('THIRD_PARTY_API_TOKEN');
        $envToken = $token;
        if ($envUrl == null || $envToken == null) {
            return ['status' => false, 'message' => "invalid token or url"];
        }
        // dd($files);
        //dd(env('THIRD_PARTY_API_URL'), env('THIRD_PARTY_API_TOKEN'));
        // Attach all files dynamically
        foreach ($files as $fileItem) {
            $request->attach(
                'documents[]', // Use array notation for multiple files
                file_get_contents($fileItem->getRealPath()), // Remove ['file'] since $fileItem is the UploadedFile object
                $fileItem->getClientOriginalName()
            );
        }
        // dd();
        // dd($files, $applicationId, $applicationName, $applicantId, $applicantName);
        // dd($request);
        // Send the POST request with the form data
        $response = $request->post("$envUrl/upload", [
            'ApplicationID' => $applicationId,
            'CustomerID' => $applicantId,
            'ApplicantName' => $name,
            'ProcessType' => "Application",
        ]);
        if ($response->successful()) {
            // dd($response->getBody()->getContents(), $response->json());
            $error = $response->getBody()->getContents();
            $data = $response->json() == null ? $error : $response->json();
            // dd($data, $response->json(), $response->getBody()->getContents());
            return $data; // Return full data to handle multiple URLs if needed
        }

        // \Log::error('Third-party file upload failed', [
        //     'status' => $response->status(),
        //     'response' => $response->body(),
        // ]);
        // dd($response);
        return $response->json();
    } catch (\Exception $e) {
        // dd($e);
        return $e->getMessage();
        return respond(false, $e->getMessage(), null, 500);
        // \Log::error('Error uploading file to third-party', [
        //     'error' => $e->getMessage(),
        // ]);
        return $e->getMesage();
    }
}




// function sanitizeFileName($filename)
// {
//     // Replace spaces and parentheses with underscores
//     return preg_replace('/[\s()]+/', '_', $filename);
// }

function sanitizeFileName($filename)
{
    // Original filename
    $originalFilename = $filename;//"DEVELOPMENT_OF_INSTRUCTIONAL_MATERIALS_F (3).pdf";

    // Get the file extension
    $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);

    // Get filename without extension
    $filenameWithoutExtension = pathinfo($originalFilename, PATHINFO_FILENAME);

    // Replace each space with a single underscore
    $filename = str_replace(' ', '_', $filenameWithoutExtension);

    // Replace each opening parenthesis '(' with a single underscore
    $filename = str_replace('(', '_', $filename);

    // Replace each closing parenthesis ')' with a single underscore
    $filename = str_replace(')', '_', $filename);

    // Construct new filename
    $newFilename = $filename . '.' . $extension;

    // Reattach the extension
    return $newFilename;
}

function generateCustomId(): string
{
    // Generate random segments
    $part1 = Str::upper(Str::random(8)); // 8 alphanumeric characters
    $part2 = mt_rand(1000, 9999); // 4 digits
    $part3 = Str::upper(Str::random(2)) . mt_rand(1000, 9999); // 2 letters + 4 digits

    // Combine with dashes
    return "{$part1}-{$part2}-{$part3}";
}

if (!function_exists('verifyNINFromAPI')) {
    function verifyNINFromAPI($nin)
    {
        $client = new Client();
        // $key = env('MONO_SECRET_KEY');
        $key = env('MOMO_STKEY');

        try {
            $response = $client->request('POST', 'https://api.withmono.com/v3/lookup/nin', [
                'body' => json_encode(['nin' => $nin]),
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'mono-sec-key' => $key,
                ],
                'timeout' => 10, // Optional timeout
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode === 200 && isset($body['status']) && $body['status'] === 'successful') {
                return [
                    'status' => true,
                    'message' => 'NIN verified successfully',
                    'data' => $body['data'],
                ];
            }

            return [
                'status' => false,
                'message' => $body['message'] ?? 'Verification failed',
                'data' => $body,
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $body = $response ? json_decode($response->getBody(), true) : null;

            return [
                'status' => false,
                'message' => $body['message'] ?? $e->getMessage(),
                'data' => $body ?? [],
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Unexpected error: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }
}


if (!function_exists('validateStampCode')) {
    function validateStampCode($code, $tin, $type)
    {
        $client = new Client();
        $url = env('OGIRSAPIURL');
        $token = env('OGIRSAPITOKEN');

        try {
            $response = $client->request('GET', "$url/verify-stamp-duty-payment-code", [
                'body' => json_encode([
                    'code' => $code,
                    'tin' => $tin,
                    'type' => $type,
                ]),
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => "Bearer $token",
                ],
            ]);

            $response = json_decode($response->getBody(), true);
            return respond(true, "Payment Code verified Successful!", $response['data'], 200);
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents(); // Get the response body
            $errorData = json_decode($responseBody, true);
            return respond(false, $errorData['message'], null, 400);
        }
    }
}

if(!function_exists('generateQoreidToken')){
    function generateQoreidToken()
    {
        $client = new Client();
        $key = env('QOREID_API_KEY');
        $secret = env('QOREID_SECRET_KEY');
        try {
            $response = $client->request('POST', 'https://api.qoreid.com/token', [
                'body' => '{"clientId":"' . $key . '","secret":"' . $secret . '"}',
                'headers' => [
                    'accept' => 'text/plain',
                    'content-type' => 'application/json',
                ],
            ]);

            $response = json_decode($response->getBody(), true);
            return [
                'token' => $response['accessToken'],
            ];

        } catch (\Exception $e) {
            return [
                'message' => 'Something went wrong with generating token',
                'error' => $e->getMessage(),
            ];
        }

    }
}

