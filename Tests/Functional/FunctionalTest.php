<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Functional;

use Matthias\SymfonyServiceDefinitionValidator\Compiler\ValidateServiceDefinitionsPass;
use Matthias\SymfonyServiceDefinitionValidator\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();

        $configuration = new Configuration();
        $configuration->setEvaluateExpressions(true);

        $compilerPass = new ValidateServiceDefinitionsPass($configuration);

        $this->container->addCompilerPass($compilerPass, PassConfig::TYPE_AFTER_REMOVING);
    }

    public function testIfTheServiceDefinitionsAreCorrectTheContainerWillBeCompiled()
    {
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__ . '/Fixtures'));
        $loader->load('correct_service_definitions.xml');

        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__ . '/Fixtures'));
        $loader->load('reported_problems.yml');

        $this->container->compile();
    }

    public function testIfAServiceDefinitionWithAnExpressionArgumentIsCorrectTheContainerWillBeCompiled()
    {
        if (!class_exists('Symfony\Component\DependencyInjection\ExpressionLanguage')) {
            $this->markTestSkipped(
                'Expressions are not supported by this version of the DependencyInjection component'
            );
        }

        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__ . '/Fixtures'));
        $loader->load('service_definition_with_expression.xml');

        $this->container->compile();
    }

    public function testIfAServiceDefinitionIsNotCorrectAnExceptionWillBeThrown()
    {
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__ . '/Fixtures'));
        $loader->load('incorrect_service_definitions.xml');

        $this->setExpectedException(
            'Matthias\SymfonyServiceDefinitionValidator\Exception\InvalidServiceDefinitionsException'
        );
        $this->container->compile();
    }
}
