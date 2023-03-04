<?php

namespace App\Repositories;

interface InstallRepository
{
    public function getRequirements();

    public function allRequirementsLoaded();

    public function getPermissions();

    public function allPermissionsGranted();

    public function getDisablePermissions();

    public function allDisablePermissionsGranted();

    public function setEnvatoCredentials($credentials);

    public function getEnvatoCredentials();

    public function setNatureDevCredentials($credentials);

    public function getNatureDevCredentials();

    public function dbCredentialsAreValid($credentials);

    public function setDatabaseCredentials($credentials);

    public function dbDropSettings();
}
