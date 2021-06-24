<?php
namespace Sync\Integrations\Canvas\Entities;

class Section
{
    public $id;
    public $name;
    public $sisSectionId;
    public $integrationId;
    public $sisImportId;
    public $courseId;
    public $sisCourseId;
    public $startAt;
    public $endAt;
    public $enrollmentTermId;
    public $restrictEnrollmentsToSectionDates;
    public $nonxlistCourseId;
    public $totalStudents;
    public $students = [];
}
