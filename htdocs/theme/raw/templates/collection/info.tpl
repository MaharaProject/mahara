{auto_escape on}
{include file="header.tpl"}
  <div class="group-info">
        <div class="fr">
            <ul class="groupuserstatus">
                <li><a href="{$WWWROOT}collection/edit.php?id={$collection->id|safe}" class="btn-edit">{str tag=edit}</a></li>
                <li><a href="{$WWWROOT}collection/delete.php?id={$collection->id|safe}" class="btn-del">{str tag=delete}</a></li>
            </ul>
        </div>
        <ul>
            <li><div>{$collection->description|safe}</div></li>
            <li><label>{str tag=created section=collection}: </label> {$collection->ctime|safe}</li>
            <li><label>{str tag=viewcount section=collection}: </label> 
            {if $collection->views}
                {$collection->views|safe}
            {else}
                {str tag=none}
            {/if}
            </li>
            <li><label>{str tag=accessoverride section=collection}: </label> 
                {if $collection->access}
                    <a href="{$WWWROOT}view/view.php?id={$collection->access->view|safe}">{$collection->access->title|safe}</a>
                {else}
                   {str tag=none}
                {/if}
            </li>
        </ul>
    </div>
{include file="footer.tpl"}
{auto_escape off}
