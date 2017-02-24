<?php

namespace SmartlingAdrotate;

use Smartling\DbAl\WordpressContentEntities\WidgetEntity;
use Smartling\Exception\SmartlingDataReadException;
use Smartling\Helpers\ArrayHelper;
use Smartling\Helpers\MetaFieldProcessor\ReferencedContentProcessor;
use Smartling\Helpers\Parsers\IntegerParser;
use Smartling\Submissions\SubmissionEntity;
use SmartlingAdrotate\ContentTypes\ContentTypeAdrotateAd;
use SmartlingAdrotate\ContentTypes\ContentTypeAdrotateGroups;

/**
 * Class AdrotateReferencedContentProcessor
 * @package Smartling\Helpers\MetaFieldProcessor
 */
class AdrotateReferencedContentProcessor extends ReferencedContentProcessor
{
    /**
     * @param SubmissionEntity $submission
     * @param string           $fieldName
     * @param mixed            $value
     *
     * @return mixed
     */
    public function processFieldPostTranslation(SubmissionEntity $submission, $fieldName, $value)
    {
        $originalValue = $value;

        if (is_array($value)) {
            $value = ArrayHelper::first($value);
        }

        if (!IntegerParser::tryParseString($value, $value)) {
            $message = vsprintf(
                'Got bad reference number for submission id=%s metadata field=\'%s\' with value=\'%s\', expected integer > 0. Skipping.',
                [$submission->getId(), $fieldName, var_export($originalValue, true),]
            );
            $this->getLogger()->warning($message);

            return $originalValue;
        }

        if (0 === $value) {
            return $value;
        }

        try {
            $this->getLogger()->debug(
                vsprintf(
                    'Sending for translation referenced content id = \'%s\' related to submission = \'%s\'.',
                    [$value, $submission->getId()]
                )
            );

            /** @var WidgetEntity $sourceContent */
            $sourceContent = $this->getContentHelper()->readSourceContent($submission);
            $settings = $sourceContent->getSettings();
            $targetContentTpye = $settings['type'] === 'group'
                ? ContentTypeAdrotateGroups::WP_CONTENT_TYPE : ContentTypeAdrotateAd::WP_CONTENT_TYPE;
            
            // trying to detect
            $attSubmission = $this->getTranslationHelper()->tryPrepareRelatedContent(
                $targetContentTpye,
                $submission->getSourceBlogId(),
                $value,
                $submission->getTargetBlogId()
            );

            return $attSubmission->getTargetId();
        } catch (SmartlingDataReadException $e) {
            $message = vsprintf(
                'An error happened while processing referenced content with original value=%s. Keeping original value.',
                [
                    var_export($originalValue, true),
                ]
            );
            $this->getLogger()->error($message);


        } catch (\Exception $e) {
            $message = vsprintf(
                'An exception occurred while sending related item=%s, submission=%s for translation. Message: %s',
                [
                    var_export($originalValue, true),
                    $submission->getId(),
                    $e->getMessage(),
                ]
            );
            $this->getLogger()->error($message);

        }

        return $originalValue;
    }
}