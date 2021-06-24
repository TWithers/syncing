<?php
namespace Sync\Integrations\Canvas;

use Sync\Connectors\ApiConsumerInterface;
use Sync\Connectors\BaseApiConsumer;
use Sync\Connectors\CourseInterface;
use Sync\Connectors\StudentInterface;
use Sync\Entities\IntegrationEntity;
use Sync\Integrations\Canvas\Entities\Course;
use Sync\Integrations\Canvas\Entities\Student;
use function GuzzleHttp\Psr7\parse_header;

class ApiConsumer extends BaseApiConsumer implements ApiConsumerInterface
{
    /**
     * @return int
     */
    public function getIntegrationType(): int
    {
        return IntegrationEntity::INTEGRATION_TYPE_CANVAS;
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
        $currentData = json_decode($body);
        if($response->hasHeader('Link')){
            $parsed = parse_header($response->getHeader('Link'));
            $links = [];
            foreach($parsed as $arr){
                if(!isset($arr['rel'])){
                    continue;
                }
                $links[$arr['rel']] = substr($arr[0], 1, -1);
            }
            if(isset($links['next'])){
                $data = $this->makeApiRequest($links['next']);
                $currentData = array_merge($currentData,$data);
            }
        }
        return $currentData;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sync\Connectors\ApiConsumerException
     */
    public function getCourses(): array
    {
        $data = $this->makeApiRequest('/api/v1/courses?enrollment_type=teacher&include[]=total_students&include[]=public_description&include[]=term&per_page=100');

        $courses = [];
        foreach($data as $course) {
            $c = new Course();
            $c->id = $course->id;
            $c->uuid = $course->uuid;
            $c->accountId = $course->account_id ?? null;
            $c->name = $course->name;
            $c->courseCode = $course->course_code;
            $c->publicDescription = $course->public_description ?? null;
            $c->startAt = $course->start_at ?? null;
            $c->endAt = $course->end_at ?? null;
            $c->workflowState = $course->workflow_state ?? null;
            $c->enrollmentTermId = $course->enrollment_term_id ?? null;
            $c->totalStudents = $course->total_students ?? null;
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
        $course = $this->makeApiRequest('/api/v1/courses/'.$id);

        $c = new Course();
        $c->id = $course->id;
        $c->uuid = $course->uuid;
        $c->accountId = $course->account_id ?? null;
        $c->name = $course->name;
        $c->courseCode = $course->course_code;
        $c->publicDescription = $course->public_description ?? null;
        $c->startAt = $course->start_at ?? null;
        $c->endAt = $course->end_at ?? null;
        $c->workflowState = $course->workflow_state ?? null;
        $c->enrollmentTermId = $course->enrollment_term_id ?? null;
        $c->totalStudents = $course->total_students ?? null;

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
        $data = $this->makeApiRequest('/api/v1/courses/'.$courseId.'/users?per_page=100');

        $students = [];
        foreach($data as $student){
            $s = new Student();
            $s->id = $student->id;
            $s->name = $student->name;
            $s->sortableName = $student->sortable_name;
            $s->loginId = $student->login_id ?? null;
            $s->email = $student->email ?? null;
            $students[] = $s;
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
        $student = $this->makeApiRequest('/api/v1/student/'.$studentId);

        $s = new Student();
        $s->id = $student->id;
        $s->name = $student->name;
        $s->sortableName = $student->sortable_name;
        $s->loginId = $student->login_id ?? null;
        $s->email = $student->email ?? null;

        return $s;
    }
}
