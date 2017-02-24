<?php

namespace SmartlingAdrotate\ContentTypes;

use Smartling\Helpers\MetaFieldProcessor\CloneValueFieldProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ContentTypeAdrotateAd
 * @package Smartling\ContentTypes
 */
class ContentTypeAdrotateAd extends ContentTypeAdrotateBasic
{
    /**
     * The system name of Wordpress content type to make references safe.
     */
    const WP_CONTENT_TYPE = 'adrotate_ad';
    
    /**
     * ContentTypeAdrotateAd constructor.
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $di
     */
    public function __construct(ContainerBuilder $di)
    {
        parent::__construct($di);
        
        $this->registerFilters();
    }
    
    /**
     * Display name of content type, e.g.: Post
     *
     * @return string
     */
    public function getLabel()
    {
        return __('AdRotate');
    }
    
    /**
     * @inheritdoc
     */
    public function getEntityClass()
    {
        return 'SmartlingAdrotate\ContentEntities\AdrotateAdEntity';
    }
    
    /**
     * Register filters.
     */
    public function registerFilters()
    {
        $filters = [
            'metaId'   => ContentTypeAdrotateLinkmeta::WP_CONTENT_TYPE,
            'schedule' => ContentTypeAdrotateSchedule::WP_CONTENT_TYPE,
            'group'    => ContentTypeAdrotateGroups::WP_CONTENT_TYPE,
            'banner'   => ContentTypeAdrotateAd::WP_CONTENT_TYPE
        ];
        $di = $this->getContainerBuilder();
        
        foreach ($filters as $fieldName => $typeName) {
            $wrapperId = 'referenced-content.adrotate_'.$fieldName;
            $definition = $di->register($wrapperId, 'Smartling\Helpers\MetaFieldProcessor\ReferencedContentProcessor');
            $definition
                ->addArgument($di->getDefinition('logger'))
                ->addArgument($di->getDefinition('translation.helper'))
                ->addArgument($fieldName)
                ->addArgument($typeName)
                ->addMethodCall('setContentHelper', [$di->getDefinition('content.helper')]);
    
            $di->get('meta-field.processor.manager')->registerProcessor($di->get($wrapperId));
        }
        
        // Non translatable fields.
        $nonTranslatableFields = ['bannercode', 'cities', 'countries', 'paid', 'author', 'imagetype', 'type'];
        $logger = $di->get('logger');
        $contentHelper = $di->get('content.helper');
        $manager = $di->get('meta-field.processor.manager');
        $handler = new CloneValueFieldProcessor(
            '^(' . implode('|', $nonTranslatableFields) . ')$',
            $contentHelper,
            $logger
        );
        $manager->registerProcessor($handler);
        
        // Add shortcodes handler.
        add_filter('smartling_inject_shortcode', function ($items)  {
            return array_merge($items, ['adrotate']);
        });
    
        // Widget field.
        $wrapperId = 'referenced-content.adrotate_widget';
        $definition = $di->register($wrapperId, 'SmartlingAdrotate\AdrotateReferencedContentProcessor');
        $definition
            ->addArgument($di->getDefinition('logger'))
            ->addArgument($di->getDefinition('translation.helper'))
            ->addArgument('adid')
            ->addArgument('')
            ->addMethodCall('setContentHelper', [$di->getDefinition('content.helper')]);

        $di->get('meta-field.processor.manager')->registerProcessor($di->get($wrapperId));
    }
}
