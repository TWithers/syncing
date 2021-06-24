<?php
namespace Sync\Connectors;

use Sync\Entities\UserEntity;

interface StudentInterface
{
    public function convertToUser():UserEntity;
}
