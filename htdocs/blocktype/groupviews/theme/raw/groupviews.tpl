{auto_escape off}
{if $sharedviews}
    <h3>{str tag="viewssharedtogroupbyothers" section="view"}</h3>
    <p>
    <table class="fullwidth">
    {foreach from=$sharedviews item=view}
        <tr class="{cycle values='r0,r1'}">
            <td>
                <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a>
                {if $view.sharedby}
                    {str tag=by section=view}
                    {if $view.group}
                        <a href="{$WWWROOT}group/view.php?id={$view.group}">{$view.sharedby}</a>
                    {elseif $view.owner}
                        <a href="{$WWWROOT}user/view.php?id={$view.owner}">{$view.sharedby}</a>
                    {else}
                        {$view.sharedby}
                    {/if}
                {/if}
                <div>{$view.shortdescription|clean_html}</div>
                {if $view.tags}<div class="tags">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
                {if $view.template}
                <div><a href="">{str tag=copythisview section=view}</a></div>
                {/if}
            </td>
        </tr>
    {/foreach}
    </table>
    {$pagination}
    </p>
{/if}

{if $mysubmittedviews || $group_view_submission_form}
    <h3>{str tag="submitaviewtogroup" section="view"}</h3>
    {if $mysubmittedviews}
      {foreach from=$mysubmittedviews item=view}
        <div>{$view.strsubmitted}</div>
      {/foreach}
    {/if}
    {if $group_view_submission_form}
        <div>{$group_view_submission_form}</div>
    {/if}
{/if}

{if $allsubmittedviews}
    <h3>{str tag="viewssubmittedtogroup" section="view"}</h3>
    <p>
    <table class="fullwidth">
    {foreach from=$allsubmittedviews item=view}
        <tr class="{cycle values='r0,r1'}">
            <td>
                <a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a>
                {if $view.sharedby}
                    {str tag=by section=view}
                    <a href="{$WWWROOT}user/view.php?id={$view.owner}">{$view.sharedby}</a>
                {/if}
                {if $view.submittedtime}
                    <span> ({str tag=timeofsubmission section=view}: {$view.submittedtime|format_date})</span>
                {/if}
                <div>{$view.shortdescription}</div>
                {if $view.tags}<div class="tags">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
            </td>
        </tr>
    {/foreach}
    </table>
    {$pagination}
    </p>
{/if}
{/auto_escape}

