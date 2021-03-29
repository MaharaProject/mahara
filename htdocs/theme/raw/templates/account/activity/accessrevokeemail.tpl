{str tag=emailheader section=notification.email arg1=$sitename}

------------------------------------------------------------------------

{strip}
{if ($revokedbyowner)}
{str section='collection' tag='ownerhasremovedaccess' arg1=$fullname arg2=$viewtitle|safe}
{else}
{str section='collection' tag='userhasremovedaccess' arg1=$fullname arg2=$viewtitle|safe}
{/if}
{/strip}
{if $message}
{str section='collection' tag='userrevokereason'}

{$message|safe}
{/if}
------------------------------------------------------------------------
{str tag=emailfooter section=notification.email arg1=$sitename, arg2=$prefurl|clean_html|safe}
