{auto_escape off}
{include file="header.tpl"}
  <div class="group-info">
        <div class="fr">
            <ul class="groupuserstatus">
                <li><a href="{$WWWROOT}collection/edit.php?id={$collection->id|escape}" class="btn-edit">{str tag="edit"}</a></li>
                <li><a href="{$WWWROOT}collection/delete.php?id={$collection->id|escape}" class="btn-del">{str tag="delete"}</a></li>
            </ul>
        </div>
        <ul>
            <li><div>{$collection->description}</div></li>
            <li><label>{str tag=created section=collection}: </label> {$collection->ctime}</li>
            <li><label>{str tag=viewcount section=collection}: </label> 
                {$collection->viewcount}
            </li>
            <li><label>{str tag=accessoverride section=collection}: </label> 
                {if $accessoverride}
                    <a href="{$WWWROOT}view/view.php?id={$accessoverride->id}">{$accessoverride->title|safe}</a>
                {else}
                   {str tag=nooverride section=collection}
                {/if}
            </li>
        </ul>
    </div>
{include file="footer.tpl"}
{/auto_escape}
