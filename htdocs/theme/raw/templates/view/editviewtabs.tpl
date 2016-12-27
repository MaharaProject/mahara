<div class="btn-group btn-toolbar btn-group-top">

    {if $edittitle}
        <a class="btn btn-default editviews editlayout {if $selected == 'layout'}active{/if}" href="{$WWWROOT}view/editlayout.php?id={$viewid}{if $new}&new=1{/if}" title="{str tag=edittitleanddescription section=view}">
            <span class="theorder1866"><img src="{$WWWROOT}theme/raw/images/number-one-in-a-circle-grey.png"></span>
            <span class="btn-title">{str tag=edittitleanddescription section=view}</span>
            {if !$issitetemplate && can_use_skins(null, false, $issiteview)}
               + {str tag=chooseskin section=skin}
            {/if}
        </a>
    {else}
    <a class="btn btn-default editviews editlayout {if $selected == 'layout'}active{/if}" href="{$WWWROOT}view/layout.php?id={$viewid}{if $new}&new=1{/if}" title="{str tag=editlayout section=view}">
        <span class="theorder1866"><img src="{$WWWROOT}theme/raw/images/number-one-in-a-circle-grey.png"></span>
        <span class="btn-title">{str tag=editlayout section=view}</span>
    </a>
    {/if}

    <a class="btn btn-default editviews editcontent {if $selected == 'content'}active{/if}" href="{$WWWROOT}view/blocks.php?id={$viewid}{if $new}&new=1{/if}" title="{str tag=editcontent section=view}">
        <span class="theorder1866"><img src="{$WWWROOT}theme/raw/images/number-two-in-a-circle-grey.png"></span>
        <span class="btn-title">{str tag=editcontent section=view}</span>
    </a>

    {if $edittitle || $viewtype == 'share'}
        <a class="btn btn-default editviews editshare {if $selected == 'share'}active{/if}" href="{$WWWROOT}view/accessurl.php?id={$viewid}{if $collectionid}&collection={$collectionid}{/if}{if $new}&new=1{/if}">
            <span class="theorder1866"><img src="{$WWWROOT}theme/raw/images/number-three-in-a-circle-grey.png"></span>
           <span class="btn-title">{str tag=shareview section=view}</span>
        </a>
    {/if}
</div>

{if !$issitetemplate}
<div id="view-wizard-controls" class="with-heading">
<a href="{$displaylink}" id="display_page" class="">
    {str tag=displayview section=view}
    <span class="icon icon-arrow-circle-right right" role="presentation" aria-hidden="true"></span>
</a>
</div>
{else}
    &nbsp;
{/if}
