{if !$issitetemplate}

    <a class="pts pull-left with-heading" href="{$displaylink}">
        {str tag=displayview section=view}
    </a>
    {if $edittitle || $viewtype == 'profile'}
        <a class="pts plm pull-left with-heading" href="{$WWWROOT}view/access.php?id={$viewid}{if $new}&new=1{/if}">
            <span class="icon icon-unlock-alt"></span>
            {str tag=shareview section=view}
        </a>
    {/if}

{/if}

<div class="toolbar mbxl pbxl">

    <div class="btn-group btn-toolbar btn-group-top">
        <a class="btn btn-default {if $selected == 'content'}active{/if}" href="{$WWWROOT}view/blocks.php?id={$viewid}{if $new}&new=1{/if}">
            <span class="icon icon-lg icon-pencil prs"></span>
            <span class="hidden-xs">{str tag=editcontent section=view}</span>
        </a>



        <a class="btn btn-default {if $selected == 'layout'}active{/if}" href="{$WWWROOT}view/layout.php?id={$viewid}{if $new}&new=1{/if}">
            <span class="icon icon-lg icon-columns prs"></span>
            <span class="hidden-xs">{str tag=editlayout section=view}</span>
        </a>

        {if !$issitetemplate && can_use_skins(null, false, $issiteview)}
            <a class="btn btn-default {if $selected == 'skin'}active{/if}" href="{$WWWROOT}view/skin.php?id={$viewid}{if $new}&new=1{/if}">
                <span class="icon icon-lg icon-paint-brush prs"></span>
                <span class="hidden-xs">{str tag=chooseskin section=skin}</span>
            </a>
        {/if}

        {if $edittitle}
            <a class="btn btn-default {if $selected == 'title'}active{/if}" href="{$WWWROOT}view/edit.php?id={$viewid}{if $new}&new=1{/if}">
                <span class="icon icon-lg icon-cogs prs"></span>
                <span class="hidden-xs">{str tag=edittitleanddescription section=view}</span>
            </a>
        {/if}

    </div>
</div>




