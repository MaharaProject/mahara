{if $is_activity_page}
    <div class="bt-inbox card card-secondary clearfix collapsible ">
        <h2 class="title card-header js-heading">
            <a data-bs-toggle="collapse" href="#target" aria-expanded="true" class="outer-link"></a>
            {$activity->description}
            <span class="icon icon-chevron-down collapse-indicator float-end" role="presentation"
                aria-hidden="true">
            </span>
            <ul class="nav float-right">
                <li> <span class="btn">{str tag="outcome" section="collection"}: {$activity->outcome}</span></li>
                <li> <span class="btn">{str tag="outcometype" section="collection"}:
                        <div id="outcometype-{$outcome->id}" class="outcome-type">
                            <span
                                class="badge rounded-pill text-bg-{$activity->styleclass}">{$activity->outcome_type}</span>
                        </div>
                    </span>
                </li>
                <li> <span class="btn">{$achievement_switch_form}</span></li>
            </ul>
        </h2>

        <div class="block collapse hide" id="target" aria-expanded="true">
            {$activity_support_form|safe}
        </div>
</div>
{/if}
<script type="application/javascript">
    $j(".activity_support").on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const supportText = $('textarea#activity_support_' + this.id)[0].value;
        const postData = {
            supportType: this.id,
            supportText: supportText,
            activityId: {$activity->id},
            viewId: {$activity->view}
        };

        sendjsonrequest(
            '{$WWWROOT}view/activitysupport.json.php',
            postData,
            'POST', data => data,
            function(error) {
                console.log(error);
            });
    });
</script>