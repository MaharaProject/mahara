{include file="header.tpl"}

{if !$accesslists.views && !$accesslists.collections}
    {if $ADMIN}
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="mtxl ptxl mbxl pbxl">
                    <p class="lead  ptxl text-center ">
                         {str tag=youhaventcreatedanyviewsyet section=view}
                    </p>
                </div>
            </div>
        </div>
    {else}
    <div class="mtxl ptxl">
        <p class="lead mtxl ptxl text-center ">
             {str tag=youhaventcreatedanyviewsyet section=view}
        </p>
    </div>
    {/if}

{else}

    {if $accesslists.collections}
    <div class="panel panel-default">
        <h3 class="panel-heading">{str tag=Collections section=collection}</h3>
    <div class="panel-body">
    <div class="table-responsive">
    <table class="fullwidth accesslists table">
        <thead>
            <tr>
                <th>{str tag=Collections section=collection}</th>
                <th>{str tag=accesslist section=view}</th>
                <th class="al-edit text-center">{str tag=editaccess section=view}</th>
                <th class="secreturls text-center">{str tag=secreturls section=view}</th>
            </tr>
        </thead>
    {foreach from=$accesslists.collections item=collection}
            <tr class="{cycle values='r0,r1'}">
        {include file="view/accesslistrow.tpl" item=$collection}
            </tr>
    {/foreach}
        </tbody>
    </table>
    </div>
    </div>
    </div>
    {/if}

    {if $accesslists.views}
    <div class="panel panel-default">
        <h3 class="panel-heading">{str tag=accesslist section=view}</h3>
    <div class="panel-body">
    <div class="table-responsive">
    <table class="fullwidth accesslists table">
        <thead>
            <tr>
                <th>{str tag=Views section=view}</th>
            {if $accesslists.collections}
                <th>{str tag=accesslist section=view}</th>
                <th class="al-edit text-center">{str tag=editaccess section=view}</th>
                <th class="secreturls text-center">{str tag=secreturls section=view}</th>
            {else}
                <th>{str tag=accesslist section=view}</th>
                <th class="al-edit text-center">{str tag=editaccess section=view}</th>
                <th class="secreturls text-center">{str tag=secreturls section=view}</th>
            {/if}
            </tr>
        </thead>
        <tbody>
    {foreach from=$accesslists.views item=view}
            <tr class="{cycle values='r0,r1'}">
        {include file="view/accesslistrow.tpl" item=$view}
            </tr>
    {/foreach}
        </tbody>
    </table>
    </div>
    </div>
    </div>
    {/if}

{/if}
{include file="footer.tpl"}
