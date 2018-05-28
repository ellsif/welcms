<?php

namespace ellsif\WelCMS;

class ManagerRepository extends Repository
{
    public function __construct(Scheme $scheme = null, DataAccess $dataAccess = null)
    {
        $this->scheme = $scheme ? $scheme : new ManagerScheme();
        $this->columns = $this->scheme->getDefinition();
        $this
            ->addModifier('info',
                function($val) {
                    return json_encode($val, true);
                },
                function($val) {
                    return json_decode($val, true);
                }
            );
        parent::__construct($this->scheme, $dataAccess);
    }

    protected function validateUniqueManagerId($value, $id)
    {
        $managerId = $value ?? '';
        $managerRepo = WelUtil::getRepository('Manager');
        $managers = $managerRepo->list(['managerId' => $managerId]);
        return count($managers) == 0 || $managers[0]['id'] == $id;
    }

    protected function validateUniqueManagerEmail($value, $id)
    {
        $email = $value ?? '';
        $managerRepo = WelUtil::getRepository('Manager');
        $managers = $managerRepo->list(['email' => $email]);
        return count($managers) == 0 || $managers[0]['id'] == $id;
    }
}