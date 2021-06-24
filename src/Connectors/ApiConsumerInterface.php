<?php
namespace Sync\Connectors;

interface ApiConsumerInterface
{
    /**
     * @return CourseInterface[]
     */
    public function getCourses(): array;

    /**
     * @param string|int $id
     * @return CourseInterface
     */
    public function getCourseDetails($id): CourseInterface;

    /**
     * @param string|int $courseId
     * @return StudentInterface[]
     */
    public function getStudentsForCourse($courseId): array;

    /**
     * @param string|int $studentId
     * @return StudentInterface
     */
    public function getStudentDetails($studentId): StudentInterface;
}
