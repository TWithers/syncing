<?php
namespace Sync\Repositories;

class DatabaseRepository
{
    //handle database connections/queries
    //Assume everything is working and successfully executing queries and converting data to appropriate entities

    public function fetchResults(string $query):array
    {
        return [];
    }

    public function delete($data){

    }

    public function save($data){
        return $data;
    }
}
