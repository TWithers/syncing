<?php
namespace Sync\Integrations\Canvas\Entities;

use Sync\Connectors\CourseInterface;
use Sync\Entities\ClassEntity;
use Sync\Entities\IntegrationEntity;
use Sync\Entities\UserEntity;

class Course implements CourseInterface
{
    public $id;
    public $uuid;
    public $accountId;
    public $name;
    public $courseCode;
    public $publicDescription;
    public $startAt;
    public $endAt;
    public $workflowState;
    public $enrollmentTermId;
    public $totalStudents;
    public $sections = [];

    public function convertToClass():ClassEntity
    {
        $c = new ClassEntity();
        $c->integrationType = IntegrationEntity::INTEGRATION_TYPE_CANVAS;
        $c->integrationId = $this->uuid;
        $c->name = $this->name;
        return $c;
    }
}

