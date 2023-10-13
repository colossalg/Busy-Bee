<?php

declare(strict_types=1);

namespace App\Middleware;

use Colossal\Http\Message\Response;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Psr\Http\Server\{
    MiddlewareInterface,
    RequestHandlerInterface
};

class AuthMiddleware implements MiddlewareInterface
{
    private const TIMEOUT_SECONDS = 60 * 60;

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    public function getAuthId(): string
    {
        return (isset($_SESSION["auth-id"]) ? $_SESSION["auth-id"] : "");
    }

    public function isSessionActive(): bool
    {
        return (
            isset($_SESSION["auth-id"]) &&
            isset($_SESSION["auth-ts"]) &&
            ($_SESSION["auth-ts"] - time() < self::TIMEOUT_SECONDS)
        );
    }

    public function updateTimestamp(): void
    {
        $_SESSION["auth-ts"] = time();
    }

    public function startSession(string $id): void
    {
        $_SESSION["auth-id"] = $id;
        $_SESSION["auth-ts"] = time();
    }

    public function clearSession(): void
    {
        unset($_SESSION["auth-id"]);
        unset($_SESSION["auth-ts"]);
    }

    private static null|self $instance = null;

    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (!$this->isSessionActive()) {
            return (new Response())->withStatus(403);
        }

        return $handler->handle($request);
    }
}
