
{if $groupviews}
    {if $groupviews.count > 0}
        <ul id="groupviewlist" class="list-group list-unstyled list-group-unbordered mtm">
            {$groupviews.tablerows|safe}
        </ul>

        {if $groupviews.pagination}
        <div id="groupviews_page_container" class="hidden pbm ptm">
            {$groupviews.pagination|safe}
        </div>
        {/if}

        {if $groupviews.pagination_js}
        <script>
            addLoadEvent(function() {literal}{{/literal}
                {$groupviews.pagination_js|safe}
                removeElementClass('groupviews_page_container', 'hidden');
            {literal}}{/literal});
        </script>
        {/if}
    {else}
        <div class="panel-body">
            {str tag=nogroupviewsyet section=view}
        </div>
    {/if}
    <hr />
{/if}

{if $sharedviews}
    <h4 class="title list-group-item-heading pls">
        {str tag="viewssharedtogroup" section="view"}:
    </h4>
    {if $sharedviews.count > 0}
        <ul id="sharedviewlist" class="list-group list-unstyled list-group-unbordered">
            {$sharedviews.tablerows|safe}
        </ul>

        {if $sharedviews.pagination}
            <div id="sharedviews_page_container" class="hidden pbm ptm">{$sharedviews.pagination|safe}
            </div>
        {/if}

        {if $sharedviews.pagination_js}
        <script>
            addLoadEvent(function() {literal}{{/literal}
                {$sharedviews.pagination_js|safe}
                removeElementClass('sharedviews_page_container', 'hidden');
            {literal}}{/literal});
        </script>
        {/if}
    {else}
        <div class="panel-body">
            {str tag=nosharedviewsyet section=view}
        </div>
    {/if}
    <hr />
{/if}


{if $sharedcollections}
    <h4 class="title list-group-item-heading pls">
        {str tag="collectionssharedtogroup" section="collection"}:
    </h4>
    {if $sharedcollections.count > 0}
        <ul id="sharedcollectionlist" class="list-group list-unstyled list-group-unbordered">
            {$sharedcollections.tablerows|safe}
        </ul>

        {if $sharedcollections.pagination}
            <div id="sharedcollections_page_container" class="hidden pbm ptm">
            {$sharedcollections.pagination|safe}
            </div>
        {/if}

        {if $sharedcollections.pagination_js}
        <script>
            addLoadEvent(function() {literal}{{/literal}
                {$sharedcollections.pagination_js|safe}
                removeElementClass('sharedcollections_page_container', 'hidden');
            {literal}}{/literal});
        </script>
        {/if}
    {else}
        <div class="panel-body">
            {str tag=nosharedcollectionsyet section=collection}
        </div>
    {/if}
    <hr />
{/if}

{if $allsubmitted}
    <h4 class="title list-group-item-heading pls">
        {str tag="submissionstogroup" section="view"}:
    </h4>
    {if $allsubmitted.count > 0}
        <ul id="allsubmissionlist" class="list-group list-unstyled list-group-unbordered">
            {$allsubmitted.tablerows|safe}
        </ul>
        {if $allsubmitted.pagination}
            <div id="allsubmitted_page_container" class="hidden">{$allsubmitted.pagination|safe}</div>
        {/if}
        {if $allsubmitted.pagination_js}
        <script>
            addLoadEvent(function() {literal}{{/literal}
                {$allsubmitted.pagination_js|safe}
                removeElementClass('allsubmitted_page_container', 'hidden');
            {literal}}{/literal});
        </script>
        {/if}
    {else}
        <div class="panel-body">
            {str tag=nosubmittedviewscollectionsyet section=view}
        </div>
    {/if}
    <hr />
{/if}


{if $mysubmitted || $group_view_submission_form}
    <h4 class="title list-group-item-heading pls">
        {if $group_view_submission_form}
            {str tag="submittogroup" section="view"}:
        {else}
            {str tag="yoursubmissions" section="view"}:
        {/if}
    </h4>
    <ul id="groupviewlist" class="list-group list-unstyled list-group-unbordered">
        {if $mysubmitted}
            {foreach from=$mysubmitted item=item}
                <li class="list-group-item text-small text-medium {if $item.submittedstatus != '2'}pbm{/if}">
                    <span>
                        {if $item.submittedtime}
                            {str tag=youhavesubmittedon section=view arg1=$item.url arg2=$item.name arg3=$item.submittedtime|format_date}
                        {else}
                            {str tag=youhavesubmitted section=view arg1=$item.url arg2=$item.name}
                        {/if}
                        {* submittedstatus == '2' is equivalent to PENDING_RELEASE *}
                        {if $item.submittedstatus == '2'}
                        <small>{str tag=submittedpendingrelease section=view}</small>
                        {/if}
                    </span>
                </li>
            {/foreach}
        {/if}
        {if $group_view_submission_form}
        <li class="list-group-item list-group-item-default">
            <div class="submissionform">
                {$group_view_submission_form|safe}
            </div>
        </li>
        {/if}
    </ul>

{/if}
