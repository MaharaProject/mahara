{loadgroupquota}
    <div class="sidebar-header"><h3>{str tag="groupquota"}
        {contextualhelp plugintype='artefact' pluginname='file' section='groupquota_message'}</h3></div>
    <div class="sidebar-content">
    <p id="quota_message">
        {$GROUPQUOTA_MESSAGE|safe}
    </p>
    <div id="quotawrap">
{if $GROUPQUOTA_PERCENTAGE < 100}
    <div id="quota_fill" style="width: {$GROUPQUOTA_PERCENTAGE*2}px;">&nbsp;</div>
    <p id="quota_bar">
        <span id="quota_percentage">{$GROUPQUOTA_PERCENTAGE}%</span>
    </p>
{else}
    <div id="quota_fill" style="display: none; width: 200px;">&nbsp;</div>
    <p id="quota_bar_100">
        <span id="quota_percentage">{$GROUPQUOTA_PERCENTAGE}%</span>
    </p>
{/if}
	</div>
</div>
