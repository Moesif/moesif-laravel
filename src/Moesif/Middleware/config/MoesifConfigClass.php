<?php

/*
This config file is not used. This is an example of
the MoesifConfigClass 'configClass' setting where you
need to provide your actual full path.
*/

namespace Moesif\Middleware\Config;

use Illuminate\Support\Facades\Log;

class MoesifConfigClass
{
    public function maskRequestHeaders($headers) {
      $headers['header5'] = 'adding rather the removing, but should work the same.';
      $headers['header6'] = 'adding rather the removing, but should work the same.';
      return $headers;
    }

    public function maskRequestBody($body) {
      return $body;
    }

    public function maskResponseHeaders($headers) {
      $headers['header2'] = '';
      return $headers;
    }

    public function maskResponseBody($body) {
      return $body;
    }

    public function identifyUserId($request, $response) {
      Log::info('[Moesif Config] : identifyUserId is called');
      if (is_null($request->user())) {
        return 'special user';
      } else {
        $user = $request->user();
        return $user['id'];
      }
    }

    public function identifyCompanyId($request, $response) {
      Log::info('[Moesif Config] : identifyCompanyId is called');
      return "newCompanyId1234";
    }

    public function identifySessionId($request, $response) {
            Log::info('[Moesif Config] : identifySessionId is called');
      if ($request->hasSession()) {
        return $request->session()->getId();
      } else {
        return null;
      }
    }

    public function getMetadata($request, $response) {
      return array("foo"=>"laravel example", "boo"=>"custom data here you are here.");
    }

    public function skip($request, $response) {
      $myurl = $request->fullUrl();
      // a hacky way to check if string contains:
      if (strpos($myurl, 'shouldskip') !== false) {
        return true;
      }
      return false;
    }
}
