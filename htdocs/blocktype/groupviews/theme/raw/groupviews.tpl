{if $groupviews}
    <div class="groupviewsection">
        <h3 class="title">{str tag="groupviews" section="view"}</h3>
        <div class="fullwidth listing">
        {foreach from=$groupviews item=view}
            <div class="{cycle values='r0,r1'} listrow">
            {if $view.template}
                <div class="fr">{$view.form|safe}</div>
            {/if}
                <h4 class="title"><a href="{$view.fullurl}">{$view.title}</a></h4>
                <div class="detail">{$view.description|str_shorten_html:100:true|strip_tags|safe}</div>
                {if $view.tags}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
            </div>
        {/foreach}
        </div>
    </div>
{/if}

{if $sharedviews}
    <div class="groupviewsection">
        <h3 class="title">{str tag="viewssharedtogroupbyothers" section="view"}</h3>
        <div class="fullwidth listing">
        {foreach from=$sharedviews item=view}
            <div class="{cycle values='r0,r1'} listrow">
            {if $view.template}
                <div class="s fr">{$view.form|safe}</div>
            {/if}
                <h4 class="title"><a href="{$view.fullurl}">{$view.title}</a>
                {if $view.sharedby}
                    <span class="owner"> {str tag=by section=view}
                        {if $view.group}
                            <a href="{group_homepage_url($view.groupdata)}">{$view.sharedby}</a>
                        {elseif $view.owner}
                            <a href="{profile_url($view.user)}">{$view.sharedby}</a>
                        {else}
                            {$view.sharedby}
                        {/if}
                    </span>
                {/if}
                </h4>
                <div class="detail">{$view.description|str_shorten_html:100:true|strip_tags|safe}</div>
             {if $view.tags}
                <div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$view.owner tags=$view.tags}</div>
             {/if}
            </div>
        {/foreach}
        </div>
    </div>
{/if}


{if $mysubmitted || $group_view_submission_form}
    <div class="groupviewsection">
    {if $group_view_submission_form}
        <h3 class="title">{str tag="submittogroup" section="view"}</h3>
    {/if}
        <div class="fullwidth listing">
        {if $mysubmitted}
        {foreach from=$mysubmitted item=item}
            <div class="{cycle values='r0,r1'} submittedform">
            {if $item.submittedtime}
                {str tag=youhavesubmittedon section=view arg1=$item.url arg2=$item.name arg3=$item.submittedtime|format_date}
            {else}
                {str tag=youhavesubmitted section=view arg1=$item.url arg2=$item.name}
            {/if}
            </div>
        {/foreach}
        {/if}
        {if $group_view_submission_form}
            <div class="submissionform">{$group_view_submission_form|safe}</div>
        {/if}
        </div>
    </div>
{/if}

{if $allsubmitted}
    <div class="groupviewsection">
        <h3 class="title">{str tag="submissionstogroup" section="view"}</h3>
        <div class="fullwidth listing" id="allsubmitted">
        {foreach from=$allsubmitted item=item}
            <div class="{cycle values='r0,r1'} listrow">
                <h4 class="title"><a href="{$item.url}">{$item.name|str_shorten_text:60:true}</a>
                <span class="owner">{str tag=by section=view} <a href="{$item.ownerurl}">{$item.ownername}</a></span></h4>
                <div class="detail">{str tag=timeofsubmission section=view}: {$item.submittedtime|format_date}</div>
            </div>
        {/foreach}
        </div>
    </div>
{/if}
