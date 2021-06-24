<?php
namespace Sync\Integrations\Blackboard\Entities;

use Sync\Connectors\CourseInterface;
use Sync\Entities\ClassEntity;
use Sync\Entities\IntegrationEntity;
use Sync\Entities\UserEntity;

class Course implements CourseInterface
{
    public $id;
    public $uuid;
    public $courseId;
    public $name;
    public $description;

    public function convertToClass():ClassEntity
    {
        $c = new ClassEntity();
        $c->integrationType = IntegrationEntity::INTEGRATION_TYPE_BLACKBOARD;
        $c->integrationId = $this->uuid;
        $c->name = $this->name;
        return $c;
    }
}
