{include file="header.tpl"}
  <div class="group-info">
        <div class="fr">
            <ul class="groupuserstatus">
                <li><a href="{$WWWROOT}collection/edit.php?id={$collection->id}" class="btn-edit">{str tag=edit}</a></li>
                <li><a href="{$WWWROOT}collection/delete.php?id={$collection->id}" class="btn-del">{str tag=delete}</a></li>
            </ul>
        </div>
        <ul>
            <li><div>{$collection->description}</div></li>
            <li><label>{str tag=created section=collection}: </label> {$collection->ctime}</li>
            <li><label>{str tag=viewcount section=collection}: </label> 
            {if $collection->views}
                {$collection->views}
            {else}
                {str tag=none}
            {/if}
            </li>
            <li><label>{str tag=accessmaster section=collection}: </label> 
                {if $collection->access}
                    <a href="{$WWWROOT}view/view.php?id={$collection->access->view}">{$collection->access->title}</a>
                {else}
                   {str tag=none}
                {/if}
            </li>
        </ul>
    </div>
{include file="footer.tpl"}
