<?php
namespace TheCodingMachine\CompositeContainer;

use Acclimate\Container\CompositeContainer;
use Interop\Container\ContainerInterface;
use Mouf\Picotainer\Picotainer;

class CompositeContainerFactory
{
    /**
     * Creates and returns a composite container instance aggregating the discovered containers.
     * This method will also instantiate Puli and will contain entries related to Puli.
     *
     * @return CompositeContainer
     */
    public static function get() {
        $puliContainer = self::getPuliContainer();
        $discovery = $puliContainer->get('puli.discovery');
        $compositeContainer = self::buildCompositeContainer($discovery);
        $compositeContainer->addContainer($puliContainer);
        return $compositeContainer;
    }

    /**
     * Creates and returns a composite container instance aggregating the discovered containers.
     *
     * @param Discovery $discovery
     *
     * @return CompositeContainer
     */
    public static function buildCompositeContainer(Discovery $discovery)
    {
        $rootContainer = new CompositeContainer();

        $bindings = $discovery->findBindings('container-interop/ContainerFactories');

        $containers = [];
        $priorities = [];

        foreach ($bindings as $binding) {
            /* @var $binding ClassBinding */
            $containerFactoryClassName = $binding->getClassName();

            // From the factory class name, let's call the buildContainer static method to get the definitionProvider.
            $containers[] = call_user_func([ $containerFactoryClassName, 'buildContainer' ], $rootContainer, $discovery);
            $priorities[] = $binding->getParameterValue('priority');
        }

        // Sort definition providers according to their priorities.
        array_multisort($priorities, $containers);

        foreach ($containers as $container) {
            $rootContainer->addContainer($container);
        }

        return $rootContainer;
    }

    private static function getPuliContainer() {
        return new Picotainer([
            'puli.factory' => function() {
                $factoryClass = PULI_FACTORY_CLASS;
                return new $factoryClass();
            },
            'puli.repository' => function(ContainerInterface $container) {
                return $container->get('puli.factory')->createRepository();
            },
            'puli.discovery' => function(ContainerInterface $container) {
                return $container->get('puli.factory')->createDiscovery($container->get('puli.repository'));
            },
            'puli.asset_url_generator' => function(ContainerInterface $container) {
                return $container->get('puli.factory')->createUrlGenerator($container->get('puli.discovery'));
            }
        ]);
    }
}
