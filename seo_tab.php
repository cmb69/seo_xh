<?php

function seo_tab($page)
{
    global $sn, $su, $plugin_tx;

    $ptx = $plugin_tx['seo'];
    $action = XH_hsc("$sn?$su");
    $slug = XH_hsc($page['seo_slug']);
    return <<<HTML
<form action="$action" method="post">
    <label for="seo_slug">{$ptx['label_slug']}</label><br>
    <input type="text" id="seo_slug" name="seo_slug" size="50" value="$slug">
    <input type="hidden" name="save_page_data">
    <div style="text-align: right">
        <input type="submit" value="{$ptx['label_save']}">
    </div>
</form>
HTML;
}
