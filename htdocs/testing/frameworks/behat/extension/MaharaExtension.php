<?php

use Symfony\Component\Yaml\Yaml;

use Behat\Behat\Context\Context,
    Behat\Behat\Context\Initializer\ContextInitializer,
    Behat\Behat\Event\SuiteEvent;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Behat extension for mahara
 *
 * Provides multiple features directory loading
 */
class MaharaExtension implements ExtensionInterface {

    /**
     * Extension configuration ID.
     */
    const MAHARA_ID = 'mahara';

    /**
     * Loads mahara specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    public function load(ContainerBuilder $container, array $config) {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('services.yml');

        $this->loadParameters($container, $config);

        // Adding mahara formatters to the list of supported formatted.
        if (isset($config['formatters'])) {
            $container->setParameter('behat.formatter.classes', $config['formatters']);
        }
    }

    /**
     * Load test parameters.
     */
    private function loadParameters(ContainerBuilder $container, array $config) {
        // Store config in parameters array to be passed into the MaharaContext.
        $mahara_parameters = array();
        foreach ($config as $key => $value) {
            $mahara_parameters[$key] = $value;
        }
        $container->setParameter('mahara.parameters', $mahara_parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(ArrayNodeDefinition $builder) {
        $builder->
            children()->
                arrayNode('features')->
                    useAttributeAsKey('key')->
                    prototype('variable')->end()->
                end()->
                arrayNode('steps_definitions')->
                    useAttributeAsKey('key')->
                    prototype('variable')->end()->
                end()->
                arrayNode('formatters')->
                    useAttributeAsKey('key')->
                    prototype('variable')->end()->
                end()->

            end()->
        end();
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigKey() {
        return self::MAHARA_ID;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager) {
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container) {
    }

}

/**
 * MaharaContext initializer
 */
class MaharaAwareInitializer implements ContextInitializer {
    private $parameters;

    public function __construct(array $parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @param Context $context
     */
    public function supports(Context $context) {
        return ($context instanceof BehatMaharaCoreContext);
    }

    /**
     * Passes the Mahara config to the main Mahara context
     * @see Behat\Behat\Context\Initializer\InitializerInterface::initializeContext()
     * @param ContextInterface $context
     */
    public function initializeContext(Context $context) {
        $context->setMaharaParameters($this->parameters);
    }
}
