<?php
namespace Sync\Integrations\Canvas\Entities;

use Sync\Connectors\StudentInterface;
use Sync\Entities\IntegrationEntity;
use Sync\Entities\UserEntity;

class Student implements StudentInterface
{
    public $id;
    public $name;
    public $sortableName;
    public $loginId;
    public $email;

    public function convertToUser():UserEntity
    {
        $user = new UserEntity();
        $user->integrationType = IntegrationEntity::INTEGRATION_TYPE_CANVAS;
        $user->integrationId = $this->id;
        list($user->lastName, $user->firstName) = explode(", ",$this->sortableName);
        $user->email = $this->email;
        return $user;
    }
}
