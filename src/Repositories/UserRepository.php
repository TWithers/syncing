<?php
namespace Sync\Repositories;

use Sync\Entities\UserEntity;

class UserRepository extends DatabaseRepository
{
    /**
     * @param UserEntity $user
     * @return UserEntity
     */
    public function createUser(UserEntity $user):UserEntity
    {
        /** @var UserEntity $class */
        $user = $this->save($class);
        return $user;
    }

    /**
     * @param UserEntity $user
     */
    public function deleteUser(UserEntity $user)
    {
        $this->delete($user);
    }

    /**
     * @param UserEntity $user
     * @return UserEntity
     */
    public function updateUser(UserEntity $user):UserEntity
    {
        /** @var UserEntity $user */
        $user = $this->save($user);
        return $user;
    }
}
