<?php

/**
 * Static validator object runner.
 *
 */
class Fez_Validate
{
    /**
     * Instantiate validator object and run on supplied data.
     * @param <string> $validator
     * @param <mixed> $data
     */
    public static function run($validator, $data)
    {
        $valObj = new $validator();
        $log = FezLog::get();
        if($valObj->isValid($data))
        {
            return $data;
        }
        else 
        {
            $msg = $valObj->getMessages();
            $log->err($msg['msg'] . __FILE__ . ':' . __LINE__);
            return false;
        }
    }
}