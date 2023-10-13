<?php

declare(strict_types=1);

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/vendor/autoload.php";

use App\Controllers\{
    TodoController,
    UserController
};
use App\Helpers\{
    RequestHelper,
    ResponseHelper
};
use App\Middleware\AuthMiddleware;
use Colossal\Routing\Router;
use Psr\Http\Message\ResponseInterface;

session_start();
if (AuthMiddleware::getInstance()->isSessionActive()) {
    AuthMiddleware::getInstance()->updateTimestamp();
} else {
    AuthMiddleware::getInstance()->clearSession();
}

$protectedRouter = new Router();
$protectedRouter->setMiddleware(AuthMiddleware::getInstance());
$protectedRouter->setFixedStart("/protected");
$protectedRouter->addController(TodoController::class);

$router = new Router();
$router->addSubRouter($protectedRouter);
$router->addController(UserController::class);
$router->addRoute("GET", "%^/?$%", function (): ResponseInterface {
    return ResponseHelper::getHtmlFileResponse(ROOT_DIR . "/static/index.html");
});

ResponseHelper::sendResponse($router->handle(RequestHelper::parseRequest()));
