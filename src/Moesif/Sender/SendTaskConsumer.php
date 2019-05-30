<?php
namespace Moesif\Sender;

/**
 * Provides some base methods for use by a Consumer implementation
 */
abstract class SendTaskConsumer extends BaseClass {

    /**
     * @var string a token associated to a Moesif project
     */
    protected $_appId;

    /**
     * Creates a new AbstractConsumer
     * @param array $options
     */
    function __construct($applicationId, $options = array()) {
        parent::__construct($options);
        $this->_appId = $applicationId;
        if ($this->_debug()) {
            $this->_log("Instantiated new Consumer");
        }
    }
    /**
     * Encode an array to be persisted
     * @param array $params
     * @return string
     */
    protected function _encode($params) {
        return json_encode($params);
        //return base64_encode(json_encode($params));
    }
    /**
     * Handles errors that occur in a consumer
     * @param $code
     * @param $msg
     */
    protected function _handleError($code, $msg) {
        if (isset($this->_options['error_callback'])) {
            $handler = $this->_options['error_callback'];
            call_user_func($handler, $code, $msg);
        }
        if ($this->_debug()) {
            $arr = debug_backtrace();
            $class = get_class($arr[0]['object']);
            $line = $arr[0]['line'];
            $this->_log ( "[ $class - line $line ] : " . print_r($msg, true) );
        }
    }
    /**
     * Persist a batch of messages in whatever way the implementer sees fit
     * @param array $batch an array of messages to consume
     * @return boolean success or fail
     */
    abstract function persist($batch);

    /**
     * Update user data
     * @params object $userData
     * @return boolean success or fail.
     */
    abstract function updateUser($userData);

    /**
     * Update users batch data
     * @params array $batch an array of userData
     * @return boolean success or fail.
     */
    abstract function updateUsersBatch($usersBatchData);

    /**
     * Update company data
     * @params object $companyData
     * @return boolean success or fail.
     */
    abstract function updateCompany($companyData);

    /**
     * Update companies batch data
     * @params array $batch an array of companyData
     * @return boolean success or fail.
     */
    abstract function updateCompaniesBatch($companiesBatchData);
}
