<?php

namespace ellsif\WelCMS;

class TokenLogRepository extends Repository
{
    public function __construct(Scheme $scheme = null, DataAccess $dataAccess = null)
    {
        $this->scheme = $scheme ? $scheme : new TokenLogScheme();
        $this->columns = $this->scheme->getDefinition();
        parent::__construct($this->scheme, $dataAccess);
    }
}