<?php

use Symfony\Component\Config\FileLocator,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\Yaml\Yaml;

use Behat\Behat\Extension\ExtensionInterface,
    Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\Initializer\InitializerInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Gherkin\Gherkin,
    Behat\Behat\Formatter\ProgressFormatter,
    Behat\Behat\DataCollector\LoggerDataCollector,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Exception\FormatterException;


/**
 * Behat extension for mahara
 *
 * Provides multiple features directory loading (Gherkin\Loader\MaharaFeaturesSuiteLoader
 */
class MaharaExtension implements ExtensionInterface {

    /**
     * Loads mahara specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('core.xml');

        // Getting the extension parameters.
        $container->setParameter('behat.mahara.parameters', $config);

        // Adding mahara formatters to the list of supported formatted.
        if (isset($config['formatters'])) {
            $container->setParameter('behat.formatter.classes', $config['formatters']);
        }
    }

    /**
     * Setups configuration for current extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder) {
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
     * Returns compiler passes used by this extension.
     *
     * @return array
     */
    public function getCompilerPasses() {
        return array();
    }

}

/**
 * MaharaContext initializer
 */
class MaharaAwareInitializer implements InitializerInterface {
    private $parameters;

    public function __construct(array $parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @see Behat\Behat\Context\Initializer.InitializerInterface::supports()
     * @param ContextInterface $context
     */
    public function supports(ContextInterface $context) {
        return ($context instanceof MaharaContext);
    }

    /**
     * Passes the Mahara config to the main Mahara context
     * @see Behat\Behat\Context\Initializer.InitializerInterface::initialize()
     * @param ContextInterface $context
     */
    public function initialize(ContextInterface $context)
    {
        $context->setMaharaConfig($this->parameters);
    }
}

/**
 * Mahara contexts loader
 *
 * It gathers all the available steps definitions reading the
 * Mahara configuration file
 *
 */
class MaharaContext extends BehatContext {

    /**
     * Mahara features and steps definitions list
     * @var array
     */
    protected $maharaConfig;

    /**
     * Includes all the specified Mahara subcontexts
     * @param array $parameters
     */
    public function setMaharaConfig($parameters) {
        $this->maharaConfig = $parameters;

        if (!is_array($this->maharaConfig)) {
            throw new RuntimeException('There are no Mahara features nor steps definitions');
        }

        // Using the key as context identifier.
        if (!empty($this->maharaConfig['steps_definitions'])) {
            foreach ($this->maharaConfig['steps_definitions'] as $classname => $path) {
                if (file_exists($path)) {
                    require_once($path);
                    $this->useContext($classname, new $classname());
                }
            }
        }
    }
}

/**
 * Gherkin extension to load multiple features folders
 *
 * Like Mahara, Mahara has multiple features folders across all Mahara
 * plugins (including 3rd party plugins) this extension loads
 * the available features
 *
 */
class MaharaGherkin extends Gherkin {

    /**
     * Mahara config
     * @var array
     */
    protected $maharaConfig;

    /**
     * Loads the Mahara config
     *
     * @param array $parameters
     */
    public function __construct($parameters) {
        $this->maharaConfig = $parameters;
    }

    /**
     * Multiple features folders loader
     *
     * Delegates load execution to parent including filters management
     *
     * @param mixed $resource Resource to load
     * @param array $filters  Additional filters
     * @return array
     */
    public function load($resource, array $filters = array()) {

        // If a resource is specified don't overwrite the parent behaviour.
        if ($resource != '') {
            return parent::load($resource, $filters);
        }

        if (!is_array($this->maharaConfig)) {
            throw new RuntimeException('There are no Mahara features nor steps definitions');
        }

        // Loads all the features files of Mahara core and plugins.
        $features = array();
        if (!empty($this->maharaConfig['features'])) {
            foreach ($this->maharaConfig['features'] as $path) {
                if (file_exists($path)) {
                    $features = array_merge($features, parent::load($path, $filters));
                }
            }
        }
        return $features;
    }

}

/**
 * MaharaProgressFormatter
 *
 * Basic ProgressFormatter extension to add the site
 * info to the CLI output.
 *
 */
class MaharaProgressFormatter extends ProgressFormatter {

    /**
     * Adding beforeSuite event.
     *
     * @return array The event names to listen to.
     */
    public static function getSubscribedEvents()
    {
        $events = parent::getSubscribedEvents();
        $events['beforeSuite'] = 'beforeSuite';

        return $events;
    }

    /**
     * We print the site info + driver used and OS.
     *
     * At this point behat_hooks::before_suite() already
     * ran, so we have $CFG and family.
     *
     * @param SuiteEvent $event
     * @return void
     */
    public function beforeSuite(SuiteEvent $event)
    {
        global $CFG;

        require_once($CFG->docroot . '/testing/frameworks/behat/classes/util.php');

        // Calling all directly from here as we avoid more behat framework extensions.
        $runinfo = \BehatTestingUtil::get_site_info();
        $runinfo .= 'Server OS "' . PHP_OS . '"' . ', Browser: "firefox"' . PHP_EOL;
        $runinfo .= 'Started at ' . date('d-m-Y, H:i', time());

        $this->writeln($runinfo);
    }

}


return new MaharaExtension();
