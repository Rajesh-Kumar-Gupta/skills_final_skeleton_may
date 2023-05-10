<?php

namespace MedilabBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class MedilabBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/medilab/js/pimcore/startup.js'
        ];
    }
    public function getVersion(){
        return 'v1.1.0';
    }
    public function getDescription(){
        return 'Medilab custome theme';
    }
}