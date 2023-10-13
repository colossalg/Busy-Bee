<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controller;
use App\Helpers\{
    GuidHelper,
    ResponseHelper
};
use App\Middleware\AuthMiddleware;
use App\Models\TodoModel;
use Colossal\Http\Message\Response;
use Colossal\Routing\Route;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};

class TodoController extends Controller
{
    public function __construct()
    {
        $this->model = new TodoModel(AuthMiddleware::getInstance()->getAuthId());
    }

    #[Route(method: "GET", pattern: "%^/todos/get-all/?$%")]
    public function getAll(): ResponseInterface
    {
        $json = $this->model->getAll();
        if ($json === false) {
            return (new Response())
                ->withStatus(500, "Internal error: Could not fetch all todos.");
        }

        return ResponseHelper::getJsonResponse($json);
    }

    #[Route(method: "GET", pattern: "%^/todos/(?<id>" . GuidHelper::GUID_PATTERN . ")/?$%")]
    public function getById(string $id): ResponseInterface
    {
        $json = $this->model->getById($id);
        if (is_null($json)) {
            return (new Response())
                ->withStatus(404, "Not found: Can't update a todo with a non-existing ID.");
        }

        return ResponseHelper::getJsonResponse($json);
    }

    #[Route(method: "POST", pattern: "%^/todos/create/?$%")]
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->model->create(
            $request->getParsedBody()->text,
            $request->getParsedBody()->done
        );
        if (!$result) {
            return (new Response())
                ->withStatus(500, "Internal error: Could not create todo.");
        }

        return (new Response())->withStatus(200);
    }

    #[Route(method: "PUT", pattern: "%^/todos/update/(?<id>" . GuidHelper::GUID_PATTERN . ")/?$%")]
    public function update(ServerRequestInterface $request, string $id): ResponseInterface
    {
        if (is_null($this->model->getById($id))) {
            return (new Response())
                ->withStatus(404, "Not found: Can't update a todo with a non-existing ID.");
        }

        $result = $this->model->update(
            $id,
            $request->getParsedBody()->text,
            $request->getParsedBody()->done
        );
        if (!$result) {
            return (new Response())
                ->withStatus(500, "Internal error: Could not update todo.");
        }

        return (new Response())->withStatus(200);
    }

    #[Route(method: "DELETE", pattern: "%^/todos/delete/(?<id>" . GuidHelper::GUID_PATTERN . ")/?$%")]
    public function delete(string $id): ResponseInterface
    {
        if (is_null($this->model->getById($id))) {
            return (new Response())
                ->withStatus(404, "Not found: Can't delete a todo with a non-existing ID.");
        }

        if (!$this->model->delete($id)) {
            return (new Response())
                ->withStatus(500, "Internal error: Could not delete todo.");
        }

        return (new Response())->withStatus(200);
    }

    private TodoModel $model;
}
