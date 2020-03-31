<?php
namespace CjsSms;

abstract class Sms {

    public static function getInstance() {
        static $instance;
        if(!$instance) {
            $instance = new static();
        }
        return $instance;
    }

}
