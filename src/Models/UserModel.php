<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\GuidHelper;
use App\Model;

class UserModel extends Model
{
    private const MAX_USERNAME_LENGTH = 256;

    private const MAX_PASSWORD_LENGTH = 256;
    private const MIN_PASSWORD_LENGTH = 8;

    private const PASSWORD_SPECIAL_CHARS = [
        "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "=",
        "[", "]", "{", "}", "|", ";", ":", "'", "<", ">", ",", ".", "/", "?",
        "\\", "\""
    ];

    public function getByUsername(string $username): null|\stdClass
    {
        $stmt = self::getConnection()->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        $result = $stmt->fetch();

        return ($result === false ? null : $result);
    }

    public function getByUsernameAndPassword(string $username, string $password): null|\stdClass
    {
        $user = self::getByUsername($username);

        if (is_null($user) || !password_verify($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function create(string $username, string $password): bool
    {
        if (
            !is_null($this->getByUsername($username))   ||
            !$this->isUsernameValid($username)              ||
            !$this->isPasswordValid($password)
        ) {
            return null;
        }

        $id = GuidHelper::createGuid();
        if ($id === false) {
            return null;
        }

        $password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = self::getConnection()->prepare("INSERT INTO users(id, username, password) VALUES (:id, :username, :password)");
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $password);
        return $stmt->execute();
    }

    private function isUsernameValid(string $username): bool
    {
        if (strlen($username) > self::MAX_USERNAME_LENGTH) {
            return false;
        }

        return boolval(preg_match("/^[a-zA-Z0-9._\-]+$/", $username));
    }

    private function isPasswordValid(string $password): bool
    {
        if (
            strlen($password) < self::MIN_PASSWORD_LENGTH ||
            strlen($password) > self::MAX_PASSWORD_LENGTH
        ) {
            return false;
        }

        $lower = [
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m",
            "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"
        ];
        $upper = [
            "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M",
            "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"
        ];

        $containsAnyCharInArray = function (array $arr) use ($password): bool {
            foreach ($arr as $char) {
                if (str_contains($password, $char)) {
                    return true;
                }
            }
            return false;
        };

        return (
            $containsAnyCharInArray($lower) &&
            $containsAnyCharInArray($upper) &&
            $containsAnyCharInArray(self::PASSWORD_SPECIAL_CHARS)
        );
    }
}
