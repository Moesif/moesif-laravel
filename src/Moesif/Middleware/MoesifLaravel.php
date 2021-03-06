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
     * Generate GUID.
     */
    function guidv4($data)
    {
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Get Client Ip Address.
     */
    function getIp(){
        foreach (array('HTTP_X_CLIENT_IP', 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_TRUE_CLIENT_IP',
        'HTTP_X_REAL_IP', 'HTTP_X_REAL_IP',  'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (strpos($ip, ':') !== false) {
                        $ip = array_values(explode(':', $ip))[0];
                    }
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
    }

    /**
     * Get value if set, else default
     */
    function getOrElse($var, $default=null) {
        return isset($var) ? $var : $default;
    }

    /**
     * Update user.
     */
    public function updateUser($userData){
        $applicationId = config('moesif.applicationId');
        $debug = config('moesif.debug');
        $disableForking = $this->getOrElse(config('moesif.disableForking'), false);

        if (is_null($applicationId)) {
            throw new Exception('ApplicationId is missing. Please provide applicationId in moesif.php in config folder.');
        }

        if (is_null($debug)) {
            $debug = false;
        }

        $moesifApi = MoesifApi::getInstance($applicationId, ['fork'=>!$disableForking, 'debug'=>$debug]);
        $moesifApi->updateUser($userData);
    }

    /**
     * Update users in batch.
     */
    public function updateUsersBatch($usersData){
        $applicationId = config('moesif.applicationId');
        $debug = config('moesif.debug');
        $disableForking = $this->getOrElse(config('moesif.disableForking'), false);

        if (is_null($applicationId)) {
            throw new Exception('ApplicationId is missing. Please provide applicationId in moesif.php in config folder.');
        }

        if (is_null($debug)) {
            $debug = false;
        }

        $moesifApi = MoesifApi::getInstance($applicationId, ['fork'=>!$disableForking, 'debug'=>$debug]);
        $moesifApi->updateUsersBatch($usersData);
    }

    /**
     * Update company.
     */
    public function updateCompany($companyData){
        $applicationId = config('moesif.applicationId');
        $debug = config('moesif.debug');
        $disableForking = $this->getOrElse(config('moesif.disableForking'), false);

        if (is_null($applicationId)) {
            throw new Exception('ApplicationId is missing. Please provide applicationId in moesif.php in config folder.');
        }

        if (is_null($debug)) {
            $debug = false;
        }

        $moesifApi = MoesifApi::getInstance($applicationId, ['fork'=>!$disableForking, 'debug'=>$debug]);
        $moesifApi->updateCompany($companyData);
    }

    /**
     * Update companies in batch.
     */
    public function updateCompaniesBatch($companiesData){
        $applicationId = config('moesif.applicationId');
        $debug = config('moesif.debug');
        $disableForking = $this->getOrElse(config('moesif.disableForking'), false);

        if (is_null($applicationId)) {
            throw new Exception('ApplicationId is missing. Please provide applicationId in moesif.php in config folder.');
        }

        if (is_null($debug)) {
            $debug = false;
        }

        $moesifApi = MoesifApi::getInstance($applicationId, ['fork'=>!$disableForking, 'debug'=>$debug]);
        $moesifApi->updateCompaniesBatch($companiesData);
    }

    /**
     * Function for basic field validation (present and neither empty nor only white space.
     */
    function IsNullOrEmptyString($str){
        $isNullOrEmpty = false;
        if (!isset($str) || trim($str) === '') {
            $isNullOrEmpty = true;
        }
        return $isNullOrEmpty;
    }

    /**
     * Function for json validation.
     */
    function IsInValidJsonBody($requestBody) {
        $encoded_data = json_encode($requestBody);
        return (preg_match("/\\\\{3,}/", $encoded_data));
    }

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

        $configClass = config('moesif.configClass');

        $maskRequestHeaders = null;
        $maskRequestBody = null;
        $maskResponseHeaders = null;
        $maskResponseBody = null;
        $identifyUserId = null;
        $identifyCompanyId = null;
        $identifySessionId = null;
        $getMetadata = null;
        $skip = null;

        if ($configClass) {
           if (!class_exists($configClass)) {
             throw new Exception('The config class '.$configClass.' not found. Please be sure to specify full name space path.');
           }
           $configInstance = new $configClass();
           $maskRequestHeaders = array($configInstance, 'maskRequestHeaders');
           $maskRequestBody = array($configInstance, 'maskRequestBody');
           $maskResponseHeaders = array($configInstance, 'maskResponseHeaders');
           $maskResponseBody = array($configInstance, 'maskResponseBody');
           $identifyUserId = array($configInstance, 'identifyUserId');
           $identifyCompanyId = array($configInstance, 'identifyCompanyId');
           $identifySessionId = array($configInstance, 'identifySessionId');
           $getMetadata = array($configInstance, 'getMetadata');
           $skip = array($configInstance, 'skip');
        }

        $debug = config('moesif.debug');
        $disableTransactionId = config('moesif.disableTransactionId');
        $logBody = config('moesif.logBody');
        $disableForking = $this->getOrElse(config('moesif.disableForking'), false);
        $transactionId = null;

        if (is_null($debug)) {
            $debug = false;
        }

        if (is_null($logBody)) {
            $logBody = true;
        }

        if (is_null($disableTransactionId)) {
            $disableTransactionId = false;
        }

        // if skip is defined, invoke skip function.
        if (is_callable($skip)) {
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
            'ip_address' => $this->getIp()
        ];

        if (!is_null($apiVersion)) {
            $requestData['api_version'] = $apiVersion;
        }

        $requestHeaders = [];
        foreach($request->headers->keys() as $key) {
            $requestHeaders[$key] = (string) $request->headers->get($key);
        }

        // Add Transaction Id to the request headers
        if (!$disableTransactionId) {
            $tmpId = (string) $request->headers->get('X-Moesif-Transaction-Id');

            if (!is_null(isset($tmpId) ? $tmpId : null)) {
                $reqTransId = (string) $request->headers->get('X-Moesif-Transaction-Id');
                if (!is_null($reqTransId)) {
                    $transactionId = $reqTransId;
                }
                if ($this->IsNullOrEmptyString($transactionId)) {
                    $transactionId = $this->guidv4(openssl_random_pseudo_bytes(16));
                }
            }
            else {
                $transactionId = $this->guidv4(openssl_random_pseudo_bytes(16));
            }
            // Filter out the old key as HTTP Headers case are not preserved
            if(array_key_exists('x-moesif-transaction-id', $requestHeaders)) { unset($requestHeaders['x-moesif-transaction-id']); }
            // Add Transaction Id to the request headers
            $requestHeaders['X-Moesif-Transaction-Id'] = $transactionId;
        }

        // can't use headers->all() because it is an array of arrays.
        // $request->headers->all();
        if(is_callable($maskRequestHeaders)) {
            $requestData['headers'] = $maskRequestHeaders($requestHeaders);
        } else {
            $requestData['headers'] = $requestHeaders;
        }

        $requestContent = $request->getContent();
        if($logBody && !is_null($requestContent)) {
            // Log::info('request body is json');
            $requestBody = json_decode($requestContent, true);
            // Log::info('' . $requestBody);
            if (is_null($requestBody) || $this->IsInValidJsonBody($requestBody) === 1) {
                if ($debug) {
                    Log::info('[Moesif] : request body is not empty nor json, base 64 encode');
                }
              $requestData['body'] = base64_encode($requestContent);
              $requestData['transfer_encoding'] = 'base64';
            } else {
                if (is_callable($maskRequestBody)) {
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
            'status' => $response->getStatusCode()
        ];

        $responseContent = $response->getContent();
        if ($logBody && !is_null($responseContent)) {
            $jsonBody = json_decode($response->getContent(), true);

          if(is_null($jsonBody) || $this->IsInValidJsonBody($jsonBody) === 1) {
            if ($debug) {
                Log::info('[Moesif] : response body is not empty nor json, base 64 encode');
            }
            $responseData['body'] = base64_encode($responseContent);
            $responseData['transfer_encoding'] = 'base64';
          } else {
            if (is_callable($maskResponseBody)) {
                $responseData['body'] = $maskResponseBody($jsonBody);
            } else {
                $responseData['body'] = $jsonBody;
            }
            $responseData['transfer_encoding'] = 'json';
          }
        }

        $responseHeaders = [];
        foreach($response->headers->keys() as $key) {
            $responseHeaders[$key] = (string) $response->headers->get($key);
        }

        // Add Transaction Id to the response headers
        if (!is_null($transactionId)) {
            $responseHeaders['X-Moesif-Transaction-Id'] = $transactionId;
        }

        if(is_callable($maskResponseHeaders)) {
            $responseData['headers'] = $maskResponseHeaders($responseHeaders);
        } else {
            $responseData['headers'] = $responseHeaders;
        }

        $data = [
            'request' => $requestData,
            'response' => $responseData
        ];

        $user = $request->user();

        if (is_callable($identifyUserId)) {
            $data['user_id'] = $this->ensureString($identifyUserId($request, $response));
        } else if (!is_null($user)) {
            $data['user_id'] = $this->ensureString($user['id']);
        }

        // CompanyId
        if(is_callable($identifyCompanyId)) {
            $data['company_id'] = $this->ensureString($identifyCompanyId($request, $response));
        }

        if (is_callable($identifySessionId)) {
            $data['session_token'] = $this->ensureString($identifySessionId($request, $response));
        } else if ($request->hasSession()) {
            $data['session_token'] = $this->ensureString($request->session()->getId());
        }

        if (is_callable($getMetadata)) {
            $metadata = $getMetadata($request, $response);
            if (empty($metadata)) {
                $data['metadata'] = null;
            } else {
                $data['metadata'] = $metadata;
            }
        }

        $moesifApi = MoesifApi::getInstance($applicationId, ['fork'=>!$disableForking, 'debug'=>$debug]);

        // Add transaction Id to the response send to the client
        if (!is_null($transactionId)) {
            $response->headers->set('X-Moesif-Transaction-Id', $transactionId);
        }

        $data['direction'] = "Incoming";
        $data['weight'] = 1;
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
