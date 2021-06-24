<?php
namespace Sync\Repositories;

use Sync\Entities\ClassEntity;
use Sync\Entities\UserEntity;

class ClassRepository extends DatabaseRepository
{
    /**
     * @param int $instructorId
     * @return ClassEntity[]
     */
    public function getClassesForInstructor(int $instructorId):array
    {
        /** @var ClassEntity[] $classes */
        $classes = $this->fetchResults('GET CLASSES FOR INSTRUCTOR WHERE ID='.$instructorId);
        return $classes;
    }

    /**
     * @param int $classId
     * @return UserEntity[]
     */
    public function getStudentsForClass(int $classId):array
    {
        /** @var UserEntity[] $students */
        $students = $this->fetchResults('GET USERS FOR CLASS WHERE ID='.$classId);
        return $students;
    }

    /**
     * @param ClassEntity $class
     * @return ClassEntity
     */
    public function createClass(ClassEntity $class):ClassEntity
    {
        /** @var ClassEntity $class */
        $class = $this->save($class);
        return $class;
    }

    /**
     * @param ClassEntity $class
     */
    public function deleteClass(ClassEntity $class)
    {
        $this->delete($class);
    }

    /**
     * @param ClassEntity $class
     * @return ClassEntity
     */
    public function updateClass(ClassEntity $class):ClassEntity
    {
        /** @var ClassEntity $class */
        $class = $this->save($class);
        return $class;
    }
}
