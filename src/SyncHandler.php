<?php
namespace Sync;

use Sync\Connectors\ApiConsumerInterface;
use Sync\Connectors\StudentInterface;
use Sync\Entities\ClassEntity;
use Sync\Entities\IntegrationEntity;
use Sync\Repositories\ClassRepository;
use Sync\Repositories\UserRepository;

class SyncHandler
{
    protected ApiConsumerInterface $api;
    protected ClassRepository $classRepo;
    protected UserRepository $userRepo;

    private array $toKeep = [];
    private array $toCreate = [];
    private array $toUpdate = [];
    private array $toRemove = [];

    public function __construct(ApiConsumerInterface $api, ClassRepository $classRepo, UserRepository $userRepo){
        $this->api = $api;
        $this->classRepo = $classRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Main method called to sync classes and students
     *
     * @param array $courseIds
     * @param int $instructorId
     */
    public function sync(array $courseIds, int $instructorId)
    {
        $classesToSync = $this->filterRemoteCoursesById($courseIds);
        $localClasses = $this->classRepo->getClassesForInstructor($instructorId);



        foreach($classesToSync as $remoteClass) {
            $foundClass = $this->searchAndSyncClass($remoteClass, $localClasses);
            $this->searchAndSyncStudents($foundClass);
        }

        foreach($this->toCreate as $student){
            $this->userRepo->createUser($student);
        }
        foreach($this->toUpdate as $student){
            $this->userRepo->updateUser($student);
        }
        foreach($this->toRemove as $student){
            $this->userRepo->deleteUser($student);
        }

    }

    /**
     * Uses the Api Interface to fetch user courses, convert them to internal course Entities,
     * and then return only the ones requested
     *
     * @param array $courseIds
     * @return array
     */
    protected function filterRemotesCourseById(array $courseIds):array
    {
        return array_filter(
            array_map(function ($course) {
                return $course->convertToClass();
            },$this->api->getCourses()),

            function(ClassEntity $class) use ($courseIds){
                return in_array($class->integrationId, $courseIds);
            }
        );
    }

    /**
     * Searches all the local classes to see if the remote/api class exists. If it does not exist,
     * it will be created. If it does exist, it will be updated if there are changes.
     *
     * @param ClassEntity $remoteClass
     * @param array $localClasses
     * @return ClassEntity
     */
    protected function searchAndSyncClass(ClassEntity $remoteClass, array $localClasses):ClassEntity
    {
        $foundClass = null;
        foreach ($localClasses as $classToFind) {
            if ($classToFind->integrationType === $this->api->getIntegrationType() && $classToFind->integrationId === $remoteClass->integrationId) {
                $foundClass = $classToFind;
                break;
            }
        }

        if($foundClass === null) {
            $foundClass = $this->classRepo->createClass($remoteClass);
        }else{
            //If several checks were needed for updates, it would be better to create a class/method to handle comparing two objects and specific properties
            if($foundClass->name !== $remoteClass->name) {
                $foundClass->name->name = $remoteClass->name;
                $this->classRepo->updateClass($foundClass);
            }

        }
        return $foundClass;
    }

    /**
     * Loads the local class roster as well as the api/remote class roster. It then goes through a process of
     * comparing the two rosters using the integrationId for the user. If the user does not exist, they are
     * created. If the user exists, but data is different, they are updated. If they don't appear in the remote
     * class, but they appear in the local class, they are removed. All the actions are stored in an array for
     * reporting purposes upon completion, as well as mass updates/inserts/deletes to the database.
     *
     * @param ClassEntity $foundClass
     * @param array $toKeep
     * @param array $toCreate
     * @param array $toUpdate
     * @param array $toRemove
     */
    protected function searchAndSyncStudents(ClassEntity $foundClass)
    {
        $localClassRoster = $this->classRepo->getStudentsForClass($foundClass->id);

        $remoteClassRoster = array_map(function(StudentInterface $student) {
            return $student->convertToUser();
        },$this->api->getStudentsForCourse($foundClass->integrationId));

        foreach($remoteClassRoster as $remoteStudent){

            $foundStudent = null;
            foreach($localClassRoster as $localStudent){
                if($localStudent->integrationType === $this->api->getIntegrationType() && $localStudent->integrationId === $remoteStudent->integrationId){
                    $foundStudent = $localStudent;
                    $this->toKeep[] = $localStudent->id;
                    break;
                }
            }

            if($foundStudent === null){
                $this->toCreate[] = $remoteStudent;
            }else{
                if($foundStudent->firstName !== $remoteStudent->firstName || $foundStudent->lastName !== $remoteStudent->lastName || $foundStudent->email !== $remoteStudent->email){
                    $foundStudent->firstName = $remoteStudent->firstName;
                    $foundStudent->lastName = $remoteStudent->lastName;
                    $foundStudent->email = $remoteStudent->email;
                    $this->toUpdate[] = $foundStudent;
                }
            }
        }

        foreach($localClassRoster as $localStudent){
            if(!in_array($localStudent->id, $this->toKeep)){
                $this->toRemove[] = $localStudent;
            }
        }
    }

    /**
     * Returns counts for the different actions that were just preformed. Useful for
     * displaying a popup to the user to let them know how many students were just synced
     *
     * @return array
     */
    public function getCounts():array
    {
        return [
            'created' => count($this->toCreate),
            'updated' => count($this->toUpdate),
            'deleted' => count($this->toRemove),
        ];
    }

    /**
     * Returns ALL the data for the different actions. This could be useful for debugging
     * or for more in-depth reporting.
     *
     * @return array
     */
    public function getData():array
    {
        return [
            'created' => $this->toCreate,
            'updated' => $this->toUpdate,
            'deleted' => $this->toRemove,
        ];
    }

}
