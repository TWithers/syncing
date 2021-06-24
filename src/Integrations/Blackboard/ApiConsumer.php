<?php
namespace Sync\Integrations\Blackboard;

use Sync\Connectors\ApiConsumerInterface;
use Sync\Connectors\BaseApiConsumer;
use Sync\Connectors\CourseInterface;
use Sync\Connectors\StudentInterface;
use Sync\Entities\IntegrationEntity;
use Sync\Integrations\Blackboard\Entities\Course;
use Sync\Integrations\Blackboard\Entities\Student;

class ApiConsumer extends BaseApiConsumer implements ApiConsumerInterface
{
    /**
     * @return int
     */
    public function getIntegrationType(): int
    {
        return IntegrationEntity::INTEGRATION_TYPE_BLACKBOARD;
    }

    /**
     * @param string $method
     * @return \stdClass|\stdClass[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sync\Connectors\ApiConsumerException
     */
    public function makeApiRequest(string $method)
    {
        $response = $this->baseApiRequest($method);
        $body = $response->getBody()->getContents();
        $data = json_decode($body);
        if(!isset($data->results)) {
            return $data;
        }
        $resultSet = $data->results;
        if (isset($data->paging) && isset($data->paging->nextPage)) {
            return array_merge($resultSet, $this->baseApiRequest($data->paging->nextPage));
        }
        return $resultSet;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sync\Connectors\ApiConsumerException
     */
    public function getCourses(): array
    {
        $response = $this->makeApiRequest('/learn/api/public/v3/courses');
        $courses = [];
        foreach($response as $course) {
            if(!isset($course->uuid)){
                continue;
            }
            $c = new Course();
            $c->id = $course->id;
            $c->uuid = $course->uuid;
            $c->courseId = $course->courseId ?? null;
            $c->name = $course->name ?? null;
            $c->description = $course->description ?? null;
            $courses[] = $c;
        }
        return $courses;
    }

    /**
     * @param int|string $id
     * @return CourseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sync\Connectors\ApiConsumerException
     */
    public function getCourseDetails($id): CourseInterface
    {
        $course = $this->makeApiRequest('/learn/api/public/v3/courses/'.$id);
        $c = new Course();
        if(!isset($course->uuid)){
            return $c;
        }
        $c->id = $course->id;
        $c->uuid = $course->uuid;
        $c->courseId = $course->courseId ?? null;
        $c->name = $course->name ?? null;
        $c->description = $course->description ?? null;

        return $c;
    }

    /**
     * @param int|string $courseId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sync\Connectors\ApiConsumerException
     */
    public function getStudentsForCourse($courseId): array
    {
        $response = $this->makeApiRequest('/learn/api/public/v1/courses/'.$courseId.'/users?expand=user');
        $students = [];
        foreach($response as $student){
            if($student->courseRoleId !== 'Student'){
                continue;
            }
            $user = new Student();
            $user->id = $student->user->id;
            $user->userName = $student->user->userName;
            $user->email = $student->user->contact->email ?? (filter_var($student->user->userName,FILTER_VALIDATE_EMAIL) ? $student->user->userName : '');
            $user->firstName = $student->user->name->given;
            $user->lastName = $student->user->name->family;
            $students[] = $user;
        }
        return $students;
    }

    /**
     * @param int|string $studentId
     * @return StudentInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sync\Connectors\ApiConsumerException
     */
    public function getStudentDetails($studentId): StudentInterface
    {
        $student = $this->makeApiRequest('/learn/api/public/v1/students/'.$studentId);

        $user = new Student();
        $user->id = $student->user->id;
        $user->userName = $student->user->userName;
        $user->email = $student->user->contact->email ?? (filter_var($student->user->userName,FILTER_VALIDATE_EMAIL) ? $student->user->userName : '');
        $user->firstName = $student->user->name->given;
        $user->lastName = $student->user->name->family;

        return $user;
    }
}
