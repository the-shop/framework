<?php

namespace Application\Helpers;

/**
 * Class GenerateHtmlData
 * @package Application\Helpers
 */
class GenerateHtmlData
{
    /**
     * @param array $htmlOptions
     * @return bool|mixed|string
     */
    public static function generateHtml(array $htmlOptions = [])
    {
        if (array_key_exists('template', $htmlOptions) === false
            || array_key_exists('data', $htmlOptions) === false
            || array_key_exists('dataTemplate', $htmlOptions['data']) === false
            || array_key_exists('dataToFill', $htmlOptions['data']) === false
        ) {
            throw new \InvalidArgumentException('html options array not formatted correctly.', 400);
        }

        $htmlTemplate = $htmlOptions['template'];

        if (is_file($htmlOptions['template']) === true) {
            $htmlTemplate = file_get_contents($htmlOptions['template']);
        }

        if (empty($htmlOptions['data']['dataTemplate']) === false) {
            $dataTemplate = null;
            if (is_file($htmlOptions['data']['dataTemplate']) === true) {
                $dataTemplate = file_get_contents($htmlOptions['data']['dataTemplate']);
            }
            if ($dataTemplate !== null) {
                foreach ($htmlOptions['data']['dataToFill'] as $data) {
                    $dataFilledTemplate = $dataTemplate;

                    foreach ($data as $k => $v) {
                        $search = "{{" . "$" . $k . "}}";
                        $dataFilledTemplate = str_replace($search, $v, $dataFilledTemplate);
                    }

                    $htmlTemplate .= $dataFilledTemplate;
                }
            }
        }

        return $htmlTemplate;
    }
}
