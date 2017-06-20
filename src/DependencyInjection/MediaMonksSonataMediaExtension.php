<?php

namespace MediaMonks\SonataMediaBundle\DependencyInjection;

use MediaMonks\SonataMediaBundle\MediaMonksSonataMediaBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class MediaMonksSonataMediaExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('mediamonks.sonata_media.config', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (empty($config['filesystem_private']) || empty($config['filesystem_public'])) {
            throw new \Exception('Both a private and a public filesystem must be set');
        }

        $container->setAlias('mediamonks.sonata_media.filesystem.private', $config['filesystem_private']);
        $container->setAlias('mediamonks.sonata_media.filesystem.public', $config['filesystem_public']);

        if (!empty($config['model_class'])) {
            $container->getDefinition('mediamonks.sonata_media.admin.media')->replaceArgument(
                1,
                $config['model_class']
            );
            $container->setParameter('mediamonks.sonata_media.entity.class', $config['model_class']);
        }

        $container->getDefinition('mediamonks.sonata_media.glide.server')
            ->replaceArgument(
                0,
                array_merge(
                    $config['glide'],
                    [
                        'source' => new Reference($config['filesystem_private']),
                        'cache' => new Reference($config['filesystem_public']),
                    ]
                )
            );

        $container->getDefinition('mediamonks.sonata_media.provider.file')
            ->replaceArgument(0, $config['file_constraints']);

        $providerPool = $container->getDefinition('mediamonks.sonata_media.provider.pool');
        foreach ($config['providers'] as $provider) {
            $providerPool->addMethodCall('addProvider', [new Reference($provider)]);
        }

        $container->getDefinition('mediamonks.sonata_media.utility.image')
            ->replaceArgument(2, $config['redirect_url'])
            ->replaceArgument(3, $config['redirect_cache_ttl']);

        $container->getDefinition('mediamonks.sonata_media.utility.download')
            ->replaceArgument(1, new Reference($config['filesystem_private']));

        $container->getDefinition('mediamonks.sonata_media.generator.image')
            ->replaceArgument(2, $config['default_image_parameters'])
            ->replaceArgument(3, $config['fallback_image'])
            ->replaceArgument(4, $config['tmp_path'])
            ->replaceArgument(5, $config['tmp_prefix']);

        $formResource = $config['templates']['form'];
        $twigFormResourceParameterId = 'twig.form.resources';
        if ($container->hasParameter($twigFormResourceParameterId)) {
            $twigFormResources = $container->getParameter($twigFormResourceParameterId);
            if (!empty($formResource) && !in_array($formResource, $twigFormResources)) {
                $twigFormResources[] = $formResource;
            }

            $container->setParameter($twigFormResourceParameterId, $twigFormResources);
        }
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return MediaMonksSonataMediaBundle::BUNDLE_CONFIG_NAME;
    }
}