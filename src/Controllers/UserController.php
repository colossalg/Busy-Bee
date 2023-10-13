<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controller;
use App\Helpers\ResponseHelper;
use App\Middleware\AuthMiddleware;
use App\Models\UserModel;
use Colossal\Http\Message\Response;
use Colossal\Routing\Route;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};

class UserController extends Controller
{
    public function __construct()
    {
        $this->model = new UserModel();
    }

    #[Route(method: "POST", pattern: "%^/users/sign-up/?$%")]
    public function signUp(ServerRequestInterface $request): ResponseInterface
    {
        if (AuthMiddleware::getInstance()->isSessionActive()) {
            return (new Response())
                ->withStatus(409, "Request conflict: The user is already signed in, can not sign-up.");
        }

        $result = $this->model->create(
            $request->getParsedBody()->username,
            $request->getParsedBody()->password
        );
        if (!$result) {
            return (new Response())
                ->withStatus(500, "Internal error: Could not create new user.");
        }

        return (new Response())->withStatus(200);
    }

    #[Route(method: "POST", pattern: "%^/users/sign-in/?$%")]
    public function signIn(ServerRequestInterface $request): ResponseInterface
    {
        if (AuthMiddleware::getInstance()->isSessionActive()) {
            return (new Response())
                ->withStatus(409, "Request conflict: The user is already signed in, can not sign-in.");
        }

        $user = $this->model->getByUsernameAndPassword(
            $request->getParsedBody()->username,
            $request->getparsedBody()->password
        );
        if (is_null($user)) {
            return (new Response())->withStatus(401);
        }

        AuthMiddleware::getInstance()->startSession($user->id);

        return (new Response())->withStatus(200);
    }

    #[Route(method: "POST", pattern: "%^/users/sign-out/?$%")]
    public function signOut(): ResponseInterface
    {
        if (!AuthMiddleware::getInstance()->isSessionActive()) {
            return (new Response())
                ->withStatus(409, "Request conflict: The user is not signed in, can not sign-out.");
        }

        AuthMiddleware::getInstance()->clearSession();

        return (new Response())->withStatus(200);
    }

    #[Route(method: "GET", pattern: "%^/users/username-exists/(?<username>[a-zA-Z0-9._\-]+)/?$%")]
    public function usernameExists(string $username): ResponseInterface
    {
        $json = [
            "result" => !is_null($this->model->getByUsername($username))
        ];

        return ResponseHelper::getJsonResponse($json);
    }

    #[Route(method: "GET", pattern: "%^/users/is-session-active/?$%")]
    public function isSessionActive(): ResponseInterface
    {
        $json = [
            "result" => AuthMiddleware::getInstance()->isSessionActive()
        ];

        return ResponseHelper::getJsonResponse($json);
    }

    private UserModel $model;
}
