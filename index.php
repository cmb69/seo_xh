<?php

const SEO_VERSION = '@SEO_VERSION@';

if ($plugin_cf['seo']['redir_enable']) {
    seo_redirect();
}
if ($plugin_cf['seo']['canonical_enable']) {
    $hjs .= seo_canonical();
}

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

/*
** Originally authored by Holger Irmler <cmsimple@holgerirmler.de>.
*/
function seo_redirect() 
{
    global $plugin_cf, $su, $u, $hjs;

    $redir_permanent = $plugin_cf['seo']['redir_permanent']; // permanent redirect?
    $force_https = $plugin_cf['seo']['redir_force_https'];   // enforce HTTPS?
    $force_www = $plugin_cf['seo']['redir_force_www'];       // request with or without 'www.'
    $remove_index = $plugin_cf['seo']['redir_remove_index']; // remove 'index.php'?

    $parts = parse_url(CMSIMPLE_URL);
    $scheme = $parts['scheme'];
    $host = $parts['host'];
    $path = $parts['path'];
    $query_str = $_SERVER['QUERY_STRING'];

    $redir = false;

    // enforce HTTPS
    if ($force_https && ($scheme == 'http')) {
        $scheme = 'https';
        $redir = true;
    }

    // remove 'index.php'
    if ($remove_index) {
        if (strtolower(substr($path, -9)) == 'index.php') {
            $path = substr_replace($path, '', -9);
            $redir = true;
        }
    }

    if ($force_www) {
        // request with 'www.'
        if (strtolower(substr($host, 0, 4)) != 'www.') {
            $host = 'www.' . $host;
            $redir = true;
        }
    } else {
        // remove 'www.'
        if (strtolower(substr($host, 0, 4)) == 'www.') {
            $host = substr_replace($host, '', 0, 4);
            $redir = true;
        }
    }

    // redirect if necessary
    if ($redir) {
        $url = $scheme . '://' . $host . $path;
        if ($query_str != '') {
            $url .= '?' . $query_str;
        }
        if ($redir_permanent) {
            header('HTTP/1.1 301 Moved Permanently');
        } else {
            header('HTTP/1.1 302 Moved Temporarily');
        }
        header("Location: $url");
        exit;
    }
}

/**
 * Returns the canonical link element
 *
 * @return string
 */
function seo_canonical()
 {
    global $su, $u, $function, $sitemap, $mailform, $plugin_cf;

    $include = array_map('trim', explode(',', $plugin_cf['seo']['canonical_include']));
    $include[] = 'function';
    if ($function === 'search') {
        $include[]  = 'search';
    }
    $params = $_GET;
    if (count($params) > 0 && key($params) == $su) {
        array_shift($params);
    }
    ksort($params);
    $url = CMSIMPLE_URL;
    if ($su != '') {
        $query = ($su == $u[0]) ? '' : $su;
    } elseif ($sitemap) {
        $query = 'sitemap';
    } elseif ($mailform) {
        $query = 'mailform';
    } else {
        $query = '';
    }
    foreach ($params as $key => $val) {
        if (in_array($key, $include)) {
            $query .= "&$key";
            if ($val !== '') {
                $query .= "=$val";
            }
        }
    }
    if ($query != '') {
        $url .= "?$query";
    }
    return tag('link rel="canonical" href="' . XH_hsc($url) . '"') . PHP_EOL;
}
