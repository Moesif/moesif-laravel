<?php
namespace Moesif\Sender;

use Exception;

class MoesifApi extends BaseClass {

    public $_sendProducer;

    private static $_instance;

    /**
     * Instantiates a new MoesifApi instance.
     * @param $applicationId
     * @param array $options
     */
    public function __construct($applicationId, $options = array()) {
        parent::__construct($options);
        $this->_sendProducer = new SendTaskProducer($applicationId, $options);
    }
    /**
     * Returns a singleton instance of MoesifApi
     * @param $applicationId
     * @param array $options
     * @return MoesifApi
     */
    public static function getInstance($applicationId, $options = array()) {
        if(!isset(self::$_instance)) {
            self::$_instance = new MoesifApi($applicationId, $options);
        }
        return self::$_instance;
    }
    /**
     * Add an array representing a message to be sent to Moesif to the in-memory queue.
     * @param array $message
     */
    public function enqueue($message = array()) {
        $this->_sendProducer->enqueue($message);
    }
    /**
     * Add an array representing a list of messages to be sent to Moesif to a queue.
     * @param array $messages
     */
    public function enqueueAll($messages = array()) {
        $this->_sendProducer->enqueueAll($messages);
    }
    /**
     * Flush the events queue
     * @param int $desired_batch_size
     */
    public function flush($desired_batch_size = 10) {
        $this->_sendProducer->flush($desired_batch_size);
    }

    /**
     * Updates Users.
     * @param userData
     * @throws Exception
     */
    public function updateUser($userData) {
      if (is_null($userData)) {
        throw new Exception('Moesif UpdateUser with a null userData object');
      }

      if (!isset($userData['user_id'])) {
        throw new Exception('Moesif updateUser requires user_id field to be set');
      }

      $this->_sendProducer->updateUser($userData);
    }

    /**
     * Updates Users in batch.
     * @param array of userData
     * @throws Exception
     */
    public function updateUsersBatch($usersBatchData = array()) {
        $users = array();

        foreach($usersBatchData as $userData) {
            if (is_null($userData)) {
                throw new Exception('Moesif UpdateUser with a null userData object');
              }
        
              if (!isset($userData['user_id'])) {
                throw new Exception('Moesif updateUser requires user_id field to be set');
              }
              $users[] = $userData;
        }
  
        $this->_sendProducer->updateUsersBatch($users);
      }

    /**
     * Empty the events queue
     */
    public function reset() {
        $this->_sendProducer->reset();
    }

    /**
     * Track an event defined by $event associated with metadata defined by $properties
     * @param string $event
     * @param array $properties
     */
    public function track($event) {
        $this->_sendProducer->track($event);
    }

}
