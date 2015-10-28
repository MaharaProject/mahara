{include file="header.tpl"}

{if $views}
    <div id="groupviews" class="view-container">
        <div class="panel panel-default">
            <h2 class="panel-heading hidefocus" tabindex="-1">Results</h2>
            <div class="list-group">
                {foreach from=$views item=view}
                <div class="list-group-item">
                    <a href="{$view.fullurl}" class="outer-link">
                        <span class="sr-only">{$view.title}</span>
                    </a>
                    <div class="row">
                         <div class="col-md-9">
                            <h3 class="title list-group-item-heading">{$view.title}</h3>
                            {if $view.description}
                                <div class="detail">
                                    {$view.description|clean_html|safe}
                                </div>
                            {/if}
                        </div>
                        {if $view.copyform}
                        <div class="col-md-3">
                            <div class="inner-link btn-action-list">
                                <div class="btn-top-right btn-group btn-group-top only-button">
                                    {$view.copyform|safe}
                                </div>
                            </div>
                        </div>
                        {/if}
                    </div>
                </div>
                {/foreach}
            </div>
        </div>
    </div>
    <div>{$pagination|safe}</div>
{else}
<div class="no-results">{str tag="noviewstosee" section="group"}</div>
{/if}

{include file="footer.tpl"}
