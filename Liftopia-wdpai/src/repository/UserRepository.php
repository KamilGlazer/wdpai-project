<?php

require_once 'Repository.php';
require_once __DIR__.'/../models/User.php';


class UserRepository extends Repository
{
    public function getUserByEmail($email) : ?User
    {
        $statement = $this->database->connect()->prepare("SELECT * FROM public.users WHERE email = :email");
        $statement->bindParam(':email', $email,PDO::PARAM_STR);
        $statement->execute();

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if($user == false) {
            return null;
        }

        $userObject = new User(
            $user['email'],
            $user['password'],
            $user['name'],
            $user['surname'],
            $user['date_of_birth']
        );

        $userObject->setId($user['id']);
        return $userObject;
    }

    public function save(User $user) : bool {
        try {
            if ($user->getId() === null) {
                $statement = $this->database->connect()->prepare("
                INSERT INTO public.users (email, password, name, surname, date_of_birth)
                VALUES (:email, :password, :name, :surname, :date_of_birth)
            ");
            } else {
                $statement = $this->database->connect()->prepare("
                UPDATE public.users
                SET email = :email, password = :password, name = :name, surname = :surname, date_of_birth = :date_of_birth
                WHERE id_user = :id
            ");
                $statement->bindValue(':id', $user->getId(), PDO::PARAM_INT);
            }

            $statement->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
            $statement->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
            $statement->bindValue(':name', $user->getName(), PDO::PARAM_STR);
            $statement->bindValue(':surname', $user->getSurname(), PDO::PARAM_STR);
            $statement->bindValue(':date_of_birth', $user->getDateOfBirth(), PDO::PARAM_STR);

            return $statement->execute();
        } catch (PDOException $e) {
            error_log("Błąd podczas zapisywania użytkownika: " . $e->getMessage());
            return false;
        }
    }
}