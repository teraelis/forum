<?php

namespace TerAelis\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TerAelisUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
