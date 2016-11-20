<?php

const SEO_VERSION = '@SEO_VERSION@';

if (defined('XH_ADM') && XH_ADM) {
    if (function_exists('XH_registerStandardPluginMenuItems')) {
        XH_registerStandardPluginMenuItems(false);
    }
    if ($edit && $s > -1) {
        if ($plugin_cf['seo']['check_page_headings']) {
            seo_check_heading_structure($l, 1, 'message_page_headings');
        }
        if ($plugin_cf['seo']['check_internal_headings']) {
            seo_check_heading_structure(
                seo_find_internal_heading_structure($c[$s]),
                $cf['menu']['levels']+1,
                'message_internal_headings'
            );
        }
    }
    if (isset($seo) && $seo == 'true') {
        $o .= print_plugin_admin('off');
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

function seo_check_heading_structure(array $levels, $startlevel, $msgkey)
{
    global $o, $plugin_tx;

    $curlevel = $startlevel - 1;
    foreach ($levels as $level) {
        if ($level > $curlevel+1) {
            $hlevels = array_map(
                function ($level) {
                    return "h$level";
                },
                $levels
            );
            $o .= XH_message('warning', $plugin_tx['seo'][$msgkey], implode(', ', $hlevels));
            return;
        }
        $curlevel = $level;
    }
}

function seo_find_internal_heading_structure($contents)
{
    global $cf;

    $levels = array();
    $menulevels = $cf['menu']['levels']+1;
    if (preg_match_all("/<h([$menulevels-6])/", $contents, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $levels[] = $match[1];
        }
    }
    return $levels;
}

function seo_headings($html)
{
    global $cf;

    $menulevels = $cf['menu']['levels']+1;
    return preg_replace_callback(
        "/<(\/)?h([$menulevels-6])/",
        function ($matches) use ($menulevels) {
            $slash = $matches[1];
            $level = $matches[2] - ($menulevels-2);
            return "<{$slash}h$level";
        },
        $html
    );
}
