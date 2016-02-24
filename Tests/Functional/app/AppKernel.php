<?php

namespace Overblog\GraphQLBundle\Tests\Functional\app;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * AppKernel
 */
class AppKernel extends Kernel
{
    private $testCase;

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Overblog\GraphQLBundle\OverblogGraphQLBundle(),
        );
    }

    public function __construct($environment, $debug, $testCase = null)
    {
        $this->testCase = null !== $testCase ? $testCase : 'config';
        parent::__construct($environment, $debug);
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/OverblogGraphQLBundle/'.Kernel::VERSION.'/'.$this->testCase.'/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/OverblogGraphQLBundle/'.Kernel::VERSION.'/'.$this->testCase.'/logs';
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(sprintf(__DIR__.'/config/%s.yml', $this->testCase));
    }
}