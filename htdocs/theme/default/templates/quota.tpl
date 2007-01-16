{loadquota}
{if $QUOTA_MESSAGE}
{counter name="sidebar" assign=SIDEBAR_SEQUENCE}
{if $SIDEBAR_SEQUENCE > 3}{assign var=SIDEBAR_SEQUENCE value=3}{/if}
<div class="sidebar sidebar_{$SIDEBAR_SEQUENCE}">
    <h3>{str tag="quota"}</h3>
    <p>
        {$QUOTA_MESSAGE}
    </p>
    <p class="center">
        {$QUOTA_PERCENTAGE}%
    </p>
	<div class="loginbox-botcorners"><img src="{image_path imagelocation='images/sidebox_bot.gif'}" border="0" alt=""></div>
</div>
{/if}
