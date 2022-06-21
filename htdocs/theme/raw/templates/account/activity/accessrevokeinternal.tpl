<p>
{if ($revokedbyowner)}
{str section='collection' tag='ownerhasremovedaccess' arg1=$fullname arg2=$viewtitle|safe}</p>
{else}
{str section='collection' tag='userhasremovedaccess' arg1=$fullname arg2=$viewtitle|safe}</p>
{/if}
<p>
{if $message}
{str section='collection' tag='userrevokereason'}
<span style="white-space: break-spaces">
{$message|clean_html|safe}
</span>
{/if}
</p>