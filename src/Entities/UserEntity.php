<?php
namespace Sync\Entities;

class UserEntity
{

    const ROLE_STUDENT = 6;
    const ROLE_TEACHER = 5;
    const ROLE_STUDENTGENERAL = 4;
    const ROLE_TEACHERADMIN = 3;
    const ROLE_DISTRICTADMIN=2;
    const ROLE_EPCO=1;

    public $id;
    public $userRole;
    public $siteId;
    public $districtId;
    public $appId;
    public $created;
    public $deleted;
    public $integrationType;
    public $integrationId;
    public $status;
    public $firstName;
    public $lastName;
    public $email;
}
