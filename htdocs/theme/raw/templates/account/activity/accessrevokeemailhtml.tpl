<p>{str tag=emailheader section=notification.email arg1=$sitename}</p>
------------------------------------------------------------------------
<p>
{if ($revokedbyowner)}
{str section='collection' tag='ownerhasremovedaccess' arg1=$fullname arg2=$viewtitle}
{else}
{str section='collection' tag='userhasremovedaccess' arg1=$fullname arg2=$viewtitle}
{/if}
</p>
{if $message}
<p>
{str section='collection' tag='userrevokereason'}
</p>
<p>
<span style="white-space: break-spaces">
{$message|clean_html|safe}
</span>
</p>
{/if}
------------------------------------------------------------------------
<p>{str tag=emailfooter section=notification.email arg1=$sitename, arg2=$prefurl|clean_html|safe}</p>
