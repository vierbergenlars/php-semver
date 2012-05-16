<?php

namespace vierbergenlars\SemVer;

class SemVerException extends \Exception {

    protected $version = NULL;
    protected $message = NULL;

    function __construct($message, $version = NULL) {
        $this->version = $version;
        $this->message = $message;
        parent::__construct($message . ' [[' . $version . ']]');
    }

    function getMessage() {
        return $this->message;
    }

    function getVersion() {
        return $this->version;
    }

}

