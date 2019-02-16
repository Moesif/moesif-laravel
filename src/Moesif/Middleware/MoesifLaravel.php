<?php
namespace Moesif\Middleware;

use Closure;

use DateTime;
use DateTimeZone;
use Exception;

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
        $getMetadata = config('moesif.getMetadata');
        $skip = config('moesif.skip');
        $debug = config('moesif.debug');

        if (is_null($debug)) {
            $debug = false;
        }

        // if skip is defined, invoke skip function.
        if (!is_null($skip)) {
          if($skip($request, $response)) {
            if ($debug) {
              Log::info('[Moesif] : skip function returned true, so skipping this event.');
            }
            return $response;
          }
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

        $requestContent = $request->getContent();
        if(!is_null($requestContent)) {
            // Log::info('request body is json');
            $requestBody = json_decode($requestContent, true);
            // Log::info('' . $requestBody);
            if (is_null($requestBody)) {
              if ($debug) {
                Log::info('[Moesif] : request body not be empty and not json, base 64 encode');
              }
              $requestData['body'] = base64_encode($requestContent);
              $requestData['transfer_encoding'] = 'base64';
            } else {
                if (!is_null($maskRequestBody)) {
                    $requestData['body'] = $maskRequestBody($requestBody);
                } else {
                    $requestData['body'] = $requestBody;
                }
            }
        }

        $endTime = microTime(true);
        $micro = sprintf("%06d",($endTime - floor($endTime)) * 1000000);
        $endDateTime = new DateTime( date('Y-m-d H:i:s.'.$micro, $endTime) );
        $endDateTime->setTimezone(new DateTimeZone("UTC"));

        $responseData = [
            'time' => $endDateTime->format('Y-m-d\TH:i:s.uP'),
            'status' => $response->status()
        ];


        $responseContent = $response->content();
        if (!is_null($responseContent)) {
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
              // $responseData['body'] = [
              //     'moesif_error' => [
              //         'code' => 'json_parse_error',
              //         'src' => 'moesif-laravel',
              //         'msg' => ['Body is not a JSON Object or JSON Array'],
              //         'args' => [$response->content()]
              //     ]
              // ];
              if (!empty($responseContent)) {
                  $responseData['body'] = base64_encode($responseContent);
                  $responseData['transfer_encoding'] = 'base64';
              }
              // $response->content();
          }
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
            $data['user_id'] = $this->ensureString($identifyUserId($request, $response));
        } else if (!is_null($user)) {
            $data['user_id'] = $this->ensureString($user['id']);
        }

        if (!is_null($identifySessionId)) {
            $data['session_token'] = $this->ensureString($identifySessionId($request, $response));
        } else if ($request->hasSession()) {
            $data['session_token'] = $this->ensureString($request->session()->getId());
        }

        if (!is_null($getMetadata)) {
          $data['metadata'] = $getMetadata($request, $response);
        }

        $moesifApi = MoesifApi::getInstance($applicationId, ['fork'=>true, 'debug'=>$debug]);
        
        $moesifApi->track($data);
        
        return $response;
    }

    protected function ensureString($item) {
      if (is_null($item)) {
        return $item;
      }
      if (is_string($item)) {
        return $item;
      }
      return strval($item);
    }
}
