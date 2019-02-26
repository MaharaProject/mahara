{include file="header.tpl"}
{if !$accesslists.views && !$accesslists.collections}
    <p class="no-results">
        {str tag=youhaventcreatedanyviewsyet section=view}
    </p>
{else}
    {if $accesslists.views && $accesslists.collections}
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" aria-hidden="true" class="active">
                <a href="#collection-tab" class="active" aria-controls="home" aria-selected="true" role="tab" data-toggle="tab" title="{str tag=collectionaccessrules section=collection}">
                    {str tag=Collections section=collection}
                </a>
            </li>
            <li role="presentation" aria-hidden="true">
                <a href="#pages-tab" aria-controls="profile" role="tab" data-toggle="tab" title="{str tag=pageaccessrules section=view}">
                    {str tag=Views section=view}
                </a>
            </li>
        </ul>
        <div class="tab-content">
    {/if}
    {if $accesslists.collections}
        <div id="collection-tab" class="card card-secondary card{if $accesslists.views} tab-pane active{/if}">
            {if !$accesslists.views}<h2 class="card-header">{str tag=Collections section=collection}</h2>{/if}
            <table class="fullwidth accesslists table">
                <thead>
                    <tr>
                        <th>{str tag=name section=collection}</th>
                        <th>{str tag=accesslist section=view}</th>
                        <th class="al-edit text-tiny text-center active">{str tag=editaccess section=view}</th>
                        <th class="secreturls text-tiny text-center active">{str tag=secreturls section=view}</th>
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
    {/if}

    {if $accesslists.views}
    <div id="pages-tab" class="card card-secondary{if $accesslists.collections} tab-pane{/if}">
        {if !$accesslists.collections}<h2 class="card-header">{str tag=Views section=view}</h2>{/if}
        <table class="fullwidth accesslists table">
            <thead>
                <tr>
                    <th>{str tag=title section=view}</th>
                    <th>{str tag=accesslist section=view}</th>
                    <th class="al-edit text-tiny text-center active">{str tag=editaccess section=view}</th>
                    <th class="secreturls text-tiny text-center active">{str tag=secreturls section=view}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$accesslists.views item=view}
                <tr>
                {include file="view/accesslistrow.tpl" item=$view}
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
    {/if}
    {if $accesslists.views && $accesslists.collections}</div>{/if}
{/if}
{include file="footer.tpl"}
