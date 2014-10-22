{if $groupviews}
    <div class="groupviewsection">
        <h3 class="title">{str tag="groupviews" section="view"}</h3>
        <div id="groupviewlist" class="fullwidth listing">
            {$groupviews.tablerows|safe}
        </div>
    {if $groupviews.pagination}
        <div id="groupviews_page_container" class="hidden center">{$groupviews.pagination|safe}</div>
    {/if}
    {if $groupviews.pagination_js}
    <script>
        addLoadEvent(function() {literal}{{/literal}
            {$groupviews.pagination_js|safe}
            removeElementClass('groupviews_page_container', 'hidden');
        {literal}}{/literal});
    </script>
    {/if}
    </div>
{/if}

{if $sharedviews}
    <div class="groupviewsection">
        <h3 class="title">{str tag="viewssharedtogroup" section="view"}</h3>
        <div id="sharedviewlist" class="fullwidth listing">
            {$sharedviews.tablerows|safe}
        </div>
    {if $sharedviews.pagination}
        <div id="sharedviews_page_container" class="hidden center">{$sharedviews.pagination|safe}</div>
    {/if}
    {if $sharedviews.pagination_js}
    <script>
        addLoadEvent(function() {literal}{{/literal}
            {$sharedviews.pagination_js|safe}
            removeElementClass('sharedviews_page_container', 'hidden');
        {literal}}{/literal});
    </script>
    {/if}
    </div>
{/if}


{if $sharedcollections}
    <div class="groupviewsection">
        <h3 class="title">{str tag="collectionssharedtogroup" section="collection"}</h3>
        <div id="sharedcollectionlist" class="fullwidth listing">
            {$sharedcollections.tablerows|safe}
        </div>
    {if $sharedcollections.pagination}
        <div id="sharedcollections_page_container" class="hidden center">{$sharedcollections.pagination|safe}</div>
    {/if}
    {if $sharedcollections.pagination_js}
    <script>
        addLoadEvent(function() {literal}{{/literal}
            {$sharedcollections.pagination_js|safe}
            removeElementClass('sharedcollections_page_container', 'hidden');
        {literal}}{/literal});
    </script>
    {/if}
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
            {* submittedstatus == '2' is equivalent to PENDING_RELEASE *}
            {if $item.submittedstatus == '2'}- {str tag=submittedpendingrelease section=view}{/if}
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
        <div id="allsubmissionlist" class="fullwidth listing">
            {$allsubmitted.tablerows|safe}
        </div>
        {if $allsubmitted.pagination}
            <div id="allsubmitted_page_container" class="hidden center">{$allsubmitted.pagination|safe}</div>
        {/if}
        {if $allsubmitted.pagination_js}
        <script>
            addLoadEvent(function() {literal}{{/literal}
                {$allsubmitted.pagination_js|safe}
                removeElementClass('allsubmitted_page_container', 'hidden');
            {literal}}{/literal});
        </script>
        {/if}
    </div>
{/if}
