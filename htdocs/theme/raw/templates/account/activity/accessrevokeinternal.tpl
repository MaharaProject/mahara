<p>
    {str section='collection' tag='userhasremovedaccess' arg1=$fullname arg2=$viewtitle|safe}</p>
<p>
{if $message}
    {str section='collection' tag='userrevokereason'}
    {strip}
        {$message}
    {/strip}
{/if}
</p>