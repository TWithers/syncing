<?php
namespace Sync\Integrations\Blackboard\Entities;

use Sync\Connectors\StudentInterface;
use Sync\Entities\IntegrationEntity;
use Sync\Entities\UserEntity;

class Student implements StudentInterface
{
    public $id;
    public $uuid;
    public $userName;
    public $email;
    public $firstName;
    public $lastName;

    public function convertToUser():UserEntity
    {
        $user = new UserEntity();
        $user->integrationType = IntegrationEntity::INTEGRATION_TYPE_BLACKBOARD;
        $user->integrationId = $this->uuid;
        $user->firstName = $this->firstName;
        $user->lastName = $this->lastName;
        $user->email = $this->email;
        return $user;
    }
}
