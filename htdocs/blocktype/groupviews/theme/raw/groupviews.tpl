{auto_escape off}
{if $sharedviews}
    <div class="groupviewsection">
    <h5>{str tag="viewssharedtogroupbyothers" section="view"}</h5>
    <table class="fullwidth listing">
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
    </div>
{/if}

{if $mysubmittedviews || $group_view_submission_form}
    <div class="groupviewsection">
    {if $group_view_submission_form}
        <h5>{str tag="submitaviewtogroup" section="view"}</h5>
    {/if}
    {if $mysubmittedviews}
      {foreach from=$mysubmittedviews item=view}
        <div>{$view.strsubmitted}</div>
      {/foreach}
    {/if}
    {if $group_view_submission_form}
        <div>{$group_view_submission_form}</div>
    {/if}
    </div>
{/if}

{if $allsubmittedviews}
    <div class="groupviewsection">
    <h5>{str tag="viewssubmittedtogroup" section="view"}</h5>
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
    </div>
{/if}
{/auto_escape}

