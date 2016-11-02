<?php
namespace Moesif\Sender;

use Illuminate\Support\Facades\Log;

/**
 * This a Base class which other extend from to provide some very basic
 * debugging and logging functionality. It also serves to persist $_options
 *
 */
class BaseClass {
    /**
     * Default options that can be overridden via the $options constructor arg
     * @var array
     */
    private $_defaults = array(
        "max_batch_size"    => 10, // the max data size moesif will accept is 250k.
        "max_queue_size"    => 15, // the max num of items to hold in memory before flushing
        "debug"             => false, // enable/disable debug mode
        "consumer"          => "curl", // which consumer to use
        "host"              => "api.moesif.net", // the host name for api calls
        "use_ssl"           => true, // use ssl when available
        "error_callback"    => null // callback to use on consumption failures
    );
    /**
     * An array of options to be used by the moesif library.
     * @var array
     */
    protected $_options = array();
    /**
     * Construct a new BaseClass object and merge custom options with defaults
     * @param array $options
     */
    public function __construct($options = array()) {
        $options = array_merge($this->_defaults, $options);
        $this->_options = $options;
    }
    /**
     * Log a message to PHP's error log
     * @param $msg
     */
    protected function _log($msg) {
        $arr = debug_backtrace();
        $class = $arr[0]['class'];
        $line = $arr[0]['line'];
        Log::info("[ $class - line $line ] : " . $msg );
    }
    /**
     * Returns true if in debug mode, false if in production mode
     * @return bool
     */
    protected function _debug() {
        return array_key_exists("debug", $this->_options) && $this->_options["debug"] == true;
    }
}
