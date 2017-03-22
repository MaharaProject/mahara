{include file="header.tpl"}

    <div class="btn-top-right btn-group btn-group-top {if $GROUP} pagetabs{/if}">
        {$createviewform|safe}
        <form method="post" class="form-as-button pull-left" action="{$WWWROOT}view/choosetemplate.php">
            <button class="submit btn btn-default">
                <span class="icon icon-lg left icon-files-o" role="presentation" aria-hidden="true"></span>
                {str tag="copyaview" section="view"}
            </button>
            {if $GROUP}
                <input type="hidden" name="group" value="{$GROUP->id}" />
            {elseif $institution}
                <input type="hidden" name="institution" value="{$institution}">
            {/if}
        </form>
    </div>
    {$searchform|safe}

    <div class="grouppageswrap view-container">
        <div class="panel panel-default">
            <h2 id="searchresultsheading" class="panel-heading">{str tag=Results}</h2>
            {if $views}
                <div id="myviews" class="list-group">
                {$viewresults|safe}
                </div>
            {else}
                <div class="no-results">
                    {if $GROUP}
                        {str tag="noviewstosee" section="group"}
                    {elseif $institution}{str tag="noviews" section="view"}
                    {else}{str tag="youhavenoviews" section="view"}{/if}
                </div>
            {/if}
        </div>
    </div>
    <div>
        {$pagination|safe}
    </div>
{include file="footer.tpl"}
