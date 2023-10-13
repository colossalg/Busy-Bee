<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\GuidHelper;
use App\Model;

class TodoModel extends Model
{
    public function __construct(null|string $uid)
    {
        $this->uid = $uid;
    }

    public function getAll(): false|array
    {
        $stmt = self::getConnection()->prepare("SELECT * FROM todos WHERE uid = :uid ORDER BY `creation-datetime` DESC");
        $stmt->bindParam(":uid", $this->uid);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getById(string $id): null|\stdClass
    {
        $stmt = self::getConnection()->prepare("SELECT * FROM todos WHERE id = :id AND uid = :uid");
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":uid", $this->uid);
        $stmt->execute();

        $result = $stmt->fetch();

        return ($result === false ? null : $result);
    }

    public function create(string $text, bool $done): bool
    {
        $id = GuidHelper::createGuid();
        if ($id === false) {
            return null;
        }
    
        $stmt = self::getConnection()->prepare("INSERT INTO todos(id, uid, text, done) VALUES (:id, :uid, :text, :done)");
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":uid", $this->uid);
        $stmt->bindParam(":text", $text);
        $stmt->bindParam(":done", $done);
        return $stmt->execute();
    }

    public function update(string $id, string $text, bool $done): bool
    {
        $stmt = self::getConnection()->prepare(
            "UPDATE todos SET " .
            "text = :text,"     .
            "done = :done "     .
            "WHERE id = :id AND uid = :uid"
        );
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":uid", $this->uid);
        $stmt->bindParam(":text", $text);
        $stmt->bindParam(":done", $done);
        return $stmt->execute();
    }

    public function delete(string $id): bool
    {
        $stmt = self::getConnection()->prepare("DELETE FROM todos WHERE id = :id AND uid = :uid");
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":uid", $this->uid);
        return $stmt->execute();
    }

    private null|string $uid;
}
