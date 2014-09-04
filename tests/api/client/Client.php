<?php

namespace fezapi\client;

require(__DIR__ . '/../vendor/nategood/httpful/src/Httpful/Bootstrap.php');
\Httpful\Bootstrap::init();


// This class is a wrapper around Httpful\Request and can be used in
// manual and behat tests.

class Client
{

    public function __construct($inifilepath)
    {
        $this->inifile = $inifilepath;
        $this->conf = parse_ini_file($inifilepath);
    }

    /**
     * Fires off a get request ot the supplied uri.
     *
     * To include basic auth supply a username/password.
     *
     * @param bool   $parse  Whether to parse the response (xml => simplexml etc)
     * @param string $format Specify the format you want to use: 'xml'|'json'|null
     *    If null, use default response (probably html).
     * @param boolean $parse Whether to parse the response as $format
     * @return response
     **/

    static function requestGET($uri, $format = 'xml', $parse = true, $username = null, $password = null)
    {
        if ($format) {
            if (strpos($uri, '?') !== false) {
                $uri = $uri . '&format=' . $format;
            } else {
                $uri = $uri . '?format=' . $format;
            }
        }

        $req = \Httpful\Request::get($uri);
        $req->followRedirects(true);

        if ($username && $password) {
            $req->authenticateWith($username, $password);
        }

        switch ($format) {
        case 'xml':
            if ($parse) {
                $req->expectsXml();
            } else {
                $req->parseWith(function($body) {return $body;});
                $req->addHeader('Content-Type','application/xml');
                $req->addHeader('Accept','application/xml');
            }
            break;
        case 'json':
            if ($parse) {
                $req->expectsJson();
            } else {
                $req->parseWith(function($body) {return $body;});
                $req->addHeader('Content-Type','application/json');
                $req->addHeader('Accept','application/json');
            }
            break;
        default:
            if (is_null($format)) {
                // Allow for default requests, which will probably get
                // HTML.
                $req->parseWith(function($body) {return $body;});
            } else {
                $msg = "no or unrecognised format='{$format}' for uri='{$uri}'";
                throw new Exception($msg);
            }
            break;
        }
        $response = $req->sendIt();
        return $response;
    }

    /**
     * Posts to the uri in the supplied format the body. Basic auth with username/password
     *
     * @return response text
     **/
    public static function requestPOST($uri, $body, $format = 'xml', $parse = true, $username = null, $password = null, $attachment = null)
    {
        $req = \Httpful\Request::post($uri);
        $req->followRedirects(true);

        if ($username && $password) {
            $req->authenticateWith($username, $password);
        }

        if ($attachment) {
            $req->attach(array('FileUpload' => $attachment));
        }

        switch ($format) {
        case 'xml':
            if ($parse) {
                $req->expectsXml();
            } else {
                if ($body) {
                    $req->parseWith(function($body) {return $body;});
                }
                //$req->addHeader('Content-Type','application/xml');
                $req->addHeader('Accept','application/xml');
            }
            if (!$attachment) {
                $req->sendsXml();
            }
            break;
        case 'json':
            if ($parse) {
                $req->expectsJson();
            } else {
                if ($body) {
                    $req->parseWith(function($body) {return $body;});
                }
                //$req->addHeader('Content-Type','application/json');
                $req->addHeader('Accept','application/json');
            }
            if (!$attachment) {
                $req->sendsJson();
            }
            break;
        default:
            $msg = "no or unrecognised format='{$format}' for uri='{$uri}'";
            throw new Exception($msg);
            break;
        }
        // in some cases $body can be null. For example file uploads are multipart and payload is supplied via $req->attach(...)
        if ($body) {
            $req->body($body);
        }
        $response = $req->send();
        return $response;
    }
}
