<?php

declare(strict_types=1);

namespace App\Helpers;

use Colossal\Http\Message\{
    Response,
    Stream
};
use Psr\Http\Message\ResponseInterface;

class ResponseHelper
{
    public static function sendResponse(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            header("$name: " . implode(",", $values));
        }      

        if ($response->getBody()->isReadable()) {
            echo $response->getBody()->getContents(); 
        }
    }

    public static function sendStatusCodeAndDie(int $statusCode, null|string $reasonPhrase = null): void
    {
        header("HTTP/1.1 $statusCode $reasonPhrase");
        die();
    }

    public static function getHtmlFileResponse(string $htmlFilePath): ResponseInterface
    {
        $resource = fopen($htmlFilePath, "r");
        if ($resource === false) {
            self::sendStatusCodeAndDie(500, "Internal error: Could not open stream for response body.");
        }

        $body = new Stream($resource);

        return (new Response())
            ->withStatus(200)
            ->withHeader("Content-Type", "text/html")
            ->withBody($body);
    }

    public static function getJsonResponse(array|object $json): ResponseInterface
    {
        $resource = fopen("php://temp", "r+");
        if ($resource === false) {
            self::sendStatusCodeAndDie(500, "Internal error: Could not open stream for response body.");
        }

        $encodedJson = json_encode($json);
        if ($encodedJson === false) {
            self::sendStatusCodeAndDie(500, "Internal error: Could not encode response JSON.");
        }

        $body = new Stream($resource);
        $body->write($encodedJson);

        return (new Response())
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->withBody($body);
    }
}