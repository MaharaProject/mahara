{if !$r.filemissing}
    <a href="{$WWWROOT}downloadarchive.php?id={$r.eid}">{$r.filetitle}</a>
{else}
    <span class="requiredmarker" title="{str tag=filemissingdesc section=admin arg1=$r.filepath arg2=$r.filename}">{str tag=filemissing section=admin arg1=$r.filetitle}</span>
{/if}