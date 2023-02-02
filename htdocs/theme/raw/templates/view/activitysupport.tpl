{if $is_activity_page}
    <div class="bt-activitysupport card card-secondary clearfix collapsible collapsible-group">
        <h2 class="title card-header js-heading">
            <a data-bs-toggle="collapse" href="#target" aria-expanded="true" class="outer-link collapsed">
                {$activity->description}
            </a>
            <span class="icon icon-chevron-down collapse-indicator float-end" role="presentation" aria-hidden="true"></span>
        </h2>
        <div class="activity-outcome-line">
            <div class="outcome-text">{str tag="outcome" section="collection"}: {$activity->outcome}</div>
            {if $activity->outcome_type}
            <div class="outcome-text">{str tag="outcometype" section="collection"}:
                <div id="outcometype-{$outcome->id}" class="outcome-type">
                    <span class="badge rounded-pill text-bg-{$activity->styleclass}">{$activity->outcome_type}</span>
                </div>
            </div>
            {/if}
            <div class="activity-outcome-signoff">{$activity_signoff_html|safe}</div>
        </div>
        <div class="block collapse hide" id="target" aria-expanded="true">
            {$activity_support|safe}
        </div>
    </div>
{/if}

<script type="application/javascript">
    $j(".activity_support").on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const supportType = this.id;
        const supportText = $('textarea#activity_support_' + supportType)[0].value;
        const postData = {
            supportType: supportType,
            supportText: supportText,
            activityId: {$activity->id},
            viewId: {$activity->view}
        };

        sendjsonrequest(
            '{$WWWROOT}view/activitysupport.json.php',
            postData,
            'POST', data => {
                let savedSupportData = data.supportData;
                let dirtyForm = false;

                // Check support text field values against DB to decide if warning pop-up is shown when leaving page
                if (savedSupportData) {
                    let dataTypes = Object.keys(savedSupportData);

                    dataTypes.forEach(type => {
                        let savedValue = Object.getOwnPropertyDescriptor(savedSupportData, type).value.value;
                        let formValue = $('textarea#activity_support_' + type)[0].value;

                        if (formValue != savedValue) {
                            dirtyForm = true;
                            return false;
                        }
                    });

                    // if all field values match the values in the DB, reset the formchanger to allow leaving the
                    // page without a warning pop-up
                    if (dirtyForm === false) {
                        if (typeof formchangemanager !== 'undefined') {
                            formchangemanager.setFormStateById('activity_support', FORM_INIT);
                        }
                    }
                }
            },
            function(error) {
                console.log(error);
            });
    });
</script>