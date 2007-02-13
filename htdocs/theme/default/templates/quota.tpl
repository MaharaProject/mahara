{loadquota}
{if $QUOTA_MESSAGE}
{counter name="sidebar" assign=SIDEBAR_SEQUENCE}
{if $SIDEBAR_SEQUENCE > 3}{assign var=SIDEBAR_SEQUENCE value=3}{/if}
<div class="sidebar sidebar_{$SIDEBAR_SEQUENCE}">
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
	<div class="loginbox-botcorners"><img src="{theme_path location='images/sidebox_bot.gif'}" border="0" alt=""></div>
</div>
{/if}
