<?php

/**
  * @link              https://www.smartling.com
  * @since             1.0.0
  * @package           smartling-adrotate-localization
  * @wordpress-plugin
  * Plugin Name:       Smartling AdRotate localization
  * Description:       Extend Smartling Connector functionality to support localization of AdRotate adverts and groups
  * Plugin URI:        https://www.smartling.com/translation-software/wordpress-translation-plugin/
  * Author URI:        https://www.smartling.com
  * License:           GPL-3.0+
  * Network:           true
  * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
  * Version: 1.0
*/

use SmartlingAdrotate\ContentTypes\ContentTypeAdrotateAd;
use SmartlingAdrotate\ContentTypes\ContentTypeAdrotateGroups;
use SmartlingAdrotate\ContentTypes\ContentTypeAdrotateLinkmeta;
use SmartlingAdrotate\ContentTypes\ContentTypeAdrotateSchedule;

add_action('plugins_loaded', function () {
    add_action(
        'smartling_before_init',
        function (\Symfony\Component\DependencyInjection\ContainerBuilder $di) {
            // Require files.
            // Entities.
            require_once __DIR__ . '/content_entities/AdrotateBaseEntityAbstract.php';
            require_once __DIR__ . '/content_entities/AdrotateAdEntity.php';
            require_once __DIR__ . '/content_entities/AdrotateGroupsEntity.php';
            require_once __DIR__ . '/content_entities/AdrotateScheduleEntity.php';
            require_once __DIR__ . '/content_entities/AdrotateLinkmetaEntity.php';

            // Reference processor.
            require_once __DIR__ . '/AdrotateReferencedContentProcessor.php';

            // Content Types.
            require_once __DIR__ . '/content_types/ContentTypeAdrotateBasic.php';
            require_once __DIR__ . '/content_types/ContentTypeAdrotateAd.php';
            require_once __DIR__ . '/content_types/ContentTypeAdrotateGroups.php';
            require_once __DIR__ . '/content_types/ContentTypeAdrotateSchedule.php';
            require_once __DIR__ . '/content_types/ContentTypeAdrotateLinkmeta.php';

            // Adrotate bootstrap.
            ContentTypeAdrotateAd::register($di);
            ContentTypeAdrotateGroups::register($di);
            ContentTypeAdrotateLinkmeta::register($di);
            ContentTypeAdrotateSchedule::register($di);
        }
    );
});
