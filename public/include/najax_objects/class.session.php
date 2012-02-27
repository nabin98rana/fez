<?php
/*
 * Fez Devel
 * Univeristy of Queensland Library
 * Created by Matthew Smith on 20/12/2006
 * This code is licensed under the GPL, see
 * http://www.gnu.org/copyleft/gpl.html
 * 
 */
 
 
 
 class Session {

     function setSession($key, $value)
     {
        if (is_array($key)) {
            $s = &$_SESSION;
            foreach ($key as $k) {
                if (!isset($s[$k])) {
                    $s[$k] = array();
                }
                $s = &$s[$k];
            }
            $s = $value;
        } else { 
            $_SESSION[$key] = $value;
        }    
     }
     
     function getSession($key)
     {
        if (is_array($key)) {
            $s = &$_SESSION;
            foreach ($key as $k) {
                if (!isset($s[$k])) {
                    return null;
                }
                $s = &$s[$k];
            }
            return $s;
        } else {
           return @$_SESSION[$key];
        }
     }
     
    function setMessage($str, $type = 'alert')
    {
        if (!empty($_SESSION['flash'])) {
            $_SESSION['flash'] .= "<br/>\n".$str;
        } else {
            $_SESSION['flash'] = $str;
        }
        $_SESSION['flash_type'] = $type;
    }
    
    function clearMessage()
    {
        $_SESSION['flash'] = '';
        $_SESSION['flash_type'] = '';
    }
    
    function getMessage()
    {
        return @$_SESSION['flash'];
    }
    
    function getMessageType()
    {
        return @$_SESSION['flash_type'];
    }
     
     function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('setSession','getSession','setMessage','getMessage','getMessageType','clearMessage' ));
        NAJAX_Client::publicMethods($this, array('setSession','getSession','setMessage','getMessage','getMessageType','clearMessage'));
    }
 }
 