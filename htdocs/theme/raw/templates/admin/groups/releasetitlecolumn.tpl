{if $r.releasetype == 'collection'}
    <a href="{$r.url}">{$r.title}</a>
{else}
    <a href="{$WWWROOT}view/view.php?id={$r.releaseid}">{$r.title}</a>
{/if}
