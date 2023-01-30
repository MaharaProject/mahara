{if $saved_achievement_level}
<span aria-label="' . {$saved_achievement_level} . '">
    <label>{str tag='achievementlevel' section='artefact.checkpoint'}</label>
    <span class="icon-stack" style="vertical-align: centre;">
        <i class="icon-regular icon-circle icon-stack-2x"></i>
        <i class="icon-solid icon-{$saved_achievement_level} . ' icon-stack-1x"></i>
        &nbsp;&nbsp;&nbsp;
    </span>
</span>
{/if}