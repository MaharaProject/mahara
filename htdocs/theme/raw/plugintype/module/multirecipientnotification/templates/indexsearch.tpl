<form action="{$boxtype}.php" method="post" class="pieform form-inline with-heading">
    <div>
        <label>{str section='module.multirecipientnotification' tag='labelsearch'}</label>
        <input type="text" name="search" id="search" autofocus="autofocus" value="{$searchdata->searchtext}">
        <input type="hidden" name="searcharea" value="All_data">
        <button type="submit" name="buttonsubmit" class="btn-primary submitcancel submit btn">{str tag='go'}</button>
    </div>
    {if $searchdata->searchtext}
    <div>
        <div>
            <span class="accessible-hidden">{str section='activity' tag='messagetype'}</span>
            {if $searchdata->all_count > 0}
                <a class="{if $searchdata->searcharea === 'All_data'}filtered{/if}" href="{$searchdata->searchurl}All_data">
            {/if}
            {str section='module.multirecipientnotification' tag='labelall'}{if $searchdata->all_count > 0} <span class="countresults">({$searchdata->all_count})</span>{/if}
            {if $searchdata->all_count > 0}
                </a>
            {/if}
        </div>
        <div>
            {if $searchdata->sender_count > 0}
                <a class="{if $searchdata->searcharea === 'Sender'}filtered{/if}" href="{$searchdata->searchurl}Sender">
            {/if}
            {str section='module.multirecipientnotification' tag='fromuser'}{if $searchdata->sender_count > 0} <span class="countresults">({$searchdata->sender_count})</span>{/if}
            {if $searchdata->sender_count > 0}
                </a>
            {/if}
        </div>
        <div>
            {if $searchdata->sub_count > 0}
                <a class="{if $searchdata->searcharea === 'Subject'}filtered{/if}" href="{$searchdata->searchurl}Subject">
            {/if}
            {str section='activity' tag='subject'}{if $searchdata->sub_count > 0} <span class="countresults"> ({$searchdata->sub_count})</span>{/if}
            {if $searchdata->sub_count > 0}
                </a>
            {/if}
        </div>
        <div>
            {if $searchdata->mes_count > 0}
                <a class="countresults {if $searchdata->searcharea === 'Message'}filtered{/if}" href="{$searchdata->searchurl}Message">
                    {str section='module.multirecipientnotification' tag='labelmessage'} <span class="countresults">({$searchdata->mes_count})</span>
                </a>
            {/if}
        </div>
        <div>
            {if $searchdata->recipient_count > 0}
                <a class="{if $searchdata->searcharea === 'Recipient'}filtered{/if}" href="{$searchdata->searchurl}Recipient" class="btn">
            {/if}
            {str section='module.multirecipientnotification' tag='touser'} {if $searchdata->recipient_count > 0} <span class="countresults">({$searchdata->recipient_count})</span>{/if}
            {if $searchdata->recipient_count > 0}
                </a>
            {/if}
        </div>
    </div>
    {/if}
</form>
