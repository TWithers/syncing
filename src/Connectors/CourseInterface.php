<?php
namespace Sync\Connectors;

use Sync\Entities\ClassEntity;

interface CourseInterface
{
    public function convertToClass():ClassEntity;
}
