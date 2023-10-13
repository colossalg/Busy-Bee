<?php

declare(strict_types=1);

namespace App\Helpers;

use Colossal\Http\Message\{
    ServerRequest,
    Stream,
    Uri
};
use Psr\Http\Message\{
    ServerRequestInterface,
    StreamInterface
};

class RequestHelper
{
    public static function parseRequest(): ServerRequestInterface
    {
        $body       = self::getBody();
        $parsedBody = self::getParsedBody($body);
    
        $request = (new ServerRequest())
            ->withServerParams($_SERVER)
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withBody($body)
            ->withParsedBody($parsedBody)
            ->withMethod($_SERVER['REQUEST_METHOD'])
            ->withUri(Uri::createUriFromString($_SERVER['REQUEST_URI']));
    
        $headers = apache_request_headers();
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
    
        return $request;
    }

    private static function getBody(): StreamInterface
    {
        $resource = fopen("php://input", "r");
        if ($resource === false) {
            ResponseHelper::sendStatusCodeAndDie(500, "Internal error: Could not open stream for request body.");
        }
    
        return new Stream($resource);
    }

    private static function getParsedBody(StreamInterface $body): null|array|object
    {
        if (!array_key_exists("CONTENT_TYPE", $_SERVER)) {
            return null;
        }

        $safeJsonDecode = function (string $json): array|object {
            $decodedJson = json_decode($json);
            if ($decodedJson === false) {
                ResponseHelper::sendStatusCodeAndDie(500, "Internal error: Could not decode resquest JSON.");
            }
            return $decodedJson;
        };

        $isMethodPost   = $_SERVER['REQUEST_METHOD'] === "POST";
        $parsedBody     =  match($_SERVER["CONTENT_TYPE"]) {
            "application/json"                  => $safeJsonDecode($body->getContents()),
            "application/x-www-form-urlencoded" => ($isMethodPost ? $_POST : null),
            "multipart/form-data"               => ($isMethodPost ? $_POST : null)
        };

        return $parsedBody;
    }
}
