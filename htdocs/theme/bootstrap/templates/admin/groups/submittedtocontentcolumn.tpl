{if $r.group && !$r.groupdeleted}<a href="{$WWWROOT}group/view.php?id={$r.group}">{/if}
    {$r.submittedto}
{if $r.group && !$r.groupdeleted}</a>{/if}