{loadgroupquota}
<div class="panel panel-default">
    <h3 class="panel-heading">
        {str tag="groupquota"}
        <span class="pull-right">
            {contextualhelp plugintype='artefact' pluginname='file' section='groupquota_message'}
        </span>
    </h3>

    <div class="panel-body">
        <p id="quota_message">
            {$GROUPQUOTA_MESSAGE|safe}
        </p>
        <div id="quotawrap" class="progress">
            {if $GROUPQUOTA_PERCENTAGE < 100}
                <div id="quota_fill" class="progress-bar" role="progressbar" aria-valuenow="{if $GROUPQUOTA_PERCENTAGE }{$GROUPQUOTA_PERCENTAGE}{else}0{/if}" aria-valuemin="0" aria-valuemax="0" style="width: {$GROUPQUOTA_PERCENTAGE*2}px;">{$GROUPQUOTA_PERCENTAGE}%</div>
            {else}
                <div id="quota_fill" style="display: none; width: 200px;" role="progressbar" >&nbsp;</div>
                <p id="quota_bar_100">
                    <span id="quota_percentage">{$GROUPQUOTA_PERCENTAGE}%</span>
                </p>
            {/if}
       </div>
    </div>
</div>
