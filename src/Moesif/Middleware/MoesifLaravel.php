<?php
namespace Moesif\Middleware;

use Closure;

use DateTime;
use DateTimeZone;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use Moesif\Sender\MoesifApi;

// require_once(dirname(__FILE__) . "/Moesif/MoesifApi.php");

class MoesifLaravel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // do action before response

        $t = LARAVEL_START;
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
        $startDateTime = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
        $startDateTime->setTimezone(new DateTimeZone("UTC"));

        $response = $next($request);

        // after response.
        
        $applicationId = config('moesif.applicationId');
        $apiVersion = config('moesif.apiVersion');
        $maskRequestHeaders = config('moesif.maskRequestHeaders');
        $maskRequestBody = config('moesif.maskRequestBody');
        $maskResponseHeaders = config('moesif.maskResponseHeaders');
        $maskResponseBody = config('moesif.maskResponseBody');
        $identifyUserId = config('moesif.identifyUserId');
        $identifySessionId = config('moesif.identifySessionId');
        $debug = config('moesif.debug');

        if (is_null($debug)) {
            $debug = false;
        }

        if (is_null($applicationId)) {
            throw new Exception('ApplicationId is missing. Please provide applicationId in moesif.php in config folder.');
        }

        $requestData = [
            'time' => $startDateTime->format('Y-m-d\TH:i:s.uP'),
            'verb' => $request->method(),
            'uri' => $request->fullUrl(),
            'ip_address' => $request->ip()
        ];

        if (!is_null($apiVersion)) {
            $requestData['api_version'] = $apiVersion;
        }

        $requestHeaders = [];
        foreach($request->headers->keys() as $key) {
            $requestHeaders[$key] = (string) $request->headers->get($key);
        }
        // can't use headers->all() because it is an array of arrays.
        // $request->headers->all();
        if(!is_null($maskRequestHeaders)) {
            $requestData['headers'] = $maskRequestHeaders($requestHeaders);
        } else {
            $requestData['headers'] = $requestHeaders;
        }

        if($request->isJson()) {
            // Log::info('request body is json');
            $requestBody = json_decode($request->getContent(), true);
            // Log::info('' . $requestBody);
            if (!is_null($maskRequestBody)) {
                $requestData['body'] = $maskRequestBody($requestBody);
            } else {
                $requestData['body'] = $requestBody;
            }
        } else {
            //Log::info('request body is not json');
        }

        $endTime = microTime(true);
        $micro = sprintf("%06d",($endTime - floor($endTime)) * 1000000);
        $endDateTime = new DateTime( date('Y-m-d H:i:s.'.$micro, $endTime) );
        $endDateTime->setTimezone(new DateTimeZone("UTC"));

        $responseData = [
            'time' => $endDateTime->format('Y-m-d\TH:i:s.uP'),
            'status' => $response->status()
        ];

        $jsonBody = json_decode($response->content(), true);

        if(!is_null($jsonBody)) {
            if (!is_null($maskResponseBody)) {
                $responseData['body'] = $maskResponseBody($jsonBody);
            } else {
                $responseData['body'] = $jsonBody;
            }
        } else {
            // that means that json can't be parsed.
            // so send the entire string for error analysis.
            $responseData['body'] = $response->content();
        }

        $responseHeaders = [];
        foreach($response->headers->keys() as $key) {
            $responseHeaders[$key] = (string) $response->headers->get($key);
        }

        if(!is_null($maskResponseHeaders)) {
            $responseData['headers'] = $maskResponseHeaders($responseHeaders);
        } else {
            $responseData['headers'] = $responseHeaders;
        }

        $data = [
            'request' => $requestData,
            'response' => $responseData
        ];

        $user = $request->user();

        if (!is_null($identifyUserId)) {
            $data['user_id'] = $identifyUserId($request, $response);
        } else if (!is_null($user)) {
            $data['user_id'] = $user['id'];
        }

        if (!is_null($identifySessionId)) {
            $data['session_token'] = $identifySessionId($request, $response);
        } else if ($request->hasSession()) {
            $data['session_token'] = $request->session()->getId();
        } else {
            $data['session_token'] = 'none';
        }

        $moesifApi = MoesifApi::getInstance($applicationId, ['fork'=>true, 'debug'=>$debug]);

        $moesifApi->track($data);

        return $response;
    }
}
