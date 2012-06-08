{if $groupviews}
    <div class="groupviewsection">
    <h5>{str tag="groupviews" section="view"}</h5>
    <table class="fullwidth listing">
    {foreach from=$groupviews item=view}
        <tr class="{cycle values='r0,r1'}">
            <td>
                {if $view.template}
                <div class="s fr valign">{$view.form|safe}</div>
                {/if}
                <h3><a href="{$view.fullurl}">{$view.title}</a></h3>
                <div class="s">{$view.description|str_shorten_html:100:true|strip_tags|safe}</div>
                {if $view.tags}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
            </td>
        </tr>
    {/foreach}
    </table>
    </div>
{/if}

{if $sharedviews}
    <div class="groupviewsection">
    <h5>{str tag="viewssharedtogroupbyothers" section="view"}</h5>
    <table class="fullwidth listing">
    {foreach from=$sharedviews item=view}
        <tr class="{cycle values='r0,r1'}">
            <td>
                {if $view.template}
                <div class="s fr valign">{$view.form|safe}</div>
                {/if}
                <h3><a href="{$view.fullurl}">{$view.title}</a></h3>
                {if $view.sharedby}
                    <span class="owner">{str tag=by section=view}
                    {if $view.group}
                        <a href="{group_homepage_url($view.groupdata)}">{$view.sharedby}</a>
                    {elseif $view.owner}
                        <a href="{profile_url($view.user)}">{$view.sharedby}</a>
                    {else}
                        {$view.sharedby}
                    {/if}
                    </span>
                {/if}
                <div class="s">{$view.description|str_shorten_html:100:true|strip_tags|safe}</div>
                {if $view.tags}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
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
    <table class="fullwidth listing">
    {if $mysubmittedviews}
      {foreach from=$mysubmittedviews item=view}
      <tr class="{cycle values='r0,r1'}"><td class="submittedform">
      {if $view.submittedtime}
        {str tag=youhavesubmittedon section=view arg1=$view.fullurl arg2=$view.title arg3=$view.submittedtime}
      {else}
        {str tag=youhavesubmitted section=view arg1=$view.fullurl arg2=$view.title}
      {/if}
      </td></tr>
      {/foreach}
    {/if}
    {if $group_view_submission_form}
        <tr>
            <td class="submissionform">{$group_view_submission_form|safe}</td>
        </tr>
    {/if}
    </table>
    </div>
{/if}

{if $allsubmittedviews}
    <div class="groupviewsection">
    <table class="fullwidth listing">
        <tr>
          <td><h5>{str tag="viewssubmittedtogroup" section="view"}</h5></td>
          <th>{str tag=timeofsubmission section=view}</th>
        </tr>
    {foreach from=$allsubmittedviews item=view}
        <tr class="{cycle values='r0,r1'}">
          <td>
            <strong><a href="{$view.fullurl}">{$view.title|str_shorten_text:60:true}</a></strong>
            <div><a href="{profile_url($view.user)}">{$view.sharedby}</a></div>
          </td>
          <td>
            <div class="postedon nowrap">{$view.submittedtime|format_date}</div>
          </td>
        </tr>
    {/foreach}
    </table>
    </div>
{/if}
