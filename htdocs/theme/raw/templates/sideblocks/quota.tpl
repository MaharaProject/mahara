{loadquota}
    <h3>{str tag="quota"}</h3>
    <p id="quota_message">
        {$QUOTA_MESSAGE}
        {contextualhelp plugintype='artefact' pluginname='file' section='quota_message'}
    </p>
{if $QUOTA_PERCENTAGE < 100}
    <div id="quota_fill" style="width: {$QUOTA_PERCENTAGE*2}px;">&nbsp;</div>
    <p id="quota_bar">
        <span id="quota_percentage">{$QUOTA_PERCENTAGE}%</span>
    </p>
{else}
    <div id="quota_fill" style="display: none; width: {$QUOTA_PERCENTAGE*2}px;">&nbsp;</div>
    <p id="quota_bar_100">
        <span id="quota_percentage">{$QUOTA_PERCENTAGE}%</span>
    </p>
{/if}
