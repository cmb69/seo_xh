<?php

seo_slugs();

function seo_slugs()
{
    global $su, $u, $s, $cl, $pd_router;

    for ($i = 0; $i < $cl; $i++) {
        $pageData = $pd_router->find_page($i);
        if (isset($pageData['seo_slug'])) {
            $slug = trim($pageData['seo_slug']);
            if ($slug != '') {
                $u[$i] = $slug;
                if ($su == $slug) {
                    $s = $i;
                } elseif ($i == $s) {
                    $su = $slug;
                }
            }
        }
    }
}
