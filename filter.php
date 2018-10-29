<?php
/**
 *
 * @copyright 2018 BuGaSi GmbH
 */
defined('MOODLE_INTERNAL') || die();

class filter_vote2go extends moodle_text_filter
{
    private $defaultOptions = array(
        'voteid' => 'derp',
        'frameheight' => '300px',
        'framewidth' => '500px',
        'criteria' => '',
        'align' => 'center'
    );

    /**
     * Override this function to actually implement the filtering.
     *
     * @param $text some HTML content.
     * @param array $options options passed to the filters
     * @return string the HTML content after the filtering has been applied.
     */
    public function filter($text, array $options = array())
    {
        $shortcodeMatches = null;
        if (preg_match_all("/\[vote2go [^\]]+\]/", $text, $shortcodeMatches)) {
            foreach ($shortcodeMatches[0] as $shortcode) {
                $shortcodeParamsMatch =  null;
                if (preg_match_all('/(\S+)=["]?((?:.(?!["]?\s+(?:\S+)=|[>"]))+.)["]?/', $shortcode, $shortcodeParamsMatch)) {
                    $attributes = array_combine(array_map(function($value){return strtolower($value);}, $shortcodeParamsMatch[1]), $shortcodeParamsMatch[2]);
                    $atts = array_merge($this->defaultOptions, $attributes);
                    $text = str_replace($shortcode, $this->filter_vote2go_getiframe($atts), $text);
                }
            }
        }
        return $text;
    }

    function filter_vote2go_getiframe($attributes) {
        $criteria = null;
        if (strlen($attributes['criteria']) > 0) {
            $criteria = [];
            $entries = explode(';', $attributes['criteria']);
            foreach ($entries as $entry) {
                $keyVal = explode(':', $entry);
                if (sizeof($keyVal) == 2) {
                    $criteria[$keyVal[0]] = $keyVal[1];
                }
            }
        }
        $iframe = "<div class=\"vote2go_wrapper\" style=\"text-align: ${attributes['align']};\">";
        $iframe .= '<iframe ';
        $iframe .= "height=\"${attributes['frameheight']}\" ";
        $iframe .= "width=\"${attributes['framewidth']}\" ";
        $iframe .= "scrolling=\"no\" ";
        $iframe .= "src=\"https://public.vote2go.de/vote/${attributes['voteid']}?display=embedded";
        $iframe .= $this->filter_vote2go_get_criteria_string($criteria);
        $iframe .= "\"";
        $iframe .= '></iframe>';
        $iframe .= '</div>';
        return $iframe;
    }

    function filter_vote2go_get_criteria_string($criteria) {
        if ($criteria == null) {
            return "";
        }
        $asJson = json_encode($criteria);
        $asBase64 = base64_encode($asJson);
        return "&criteria=${asBase64}";
    }
}