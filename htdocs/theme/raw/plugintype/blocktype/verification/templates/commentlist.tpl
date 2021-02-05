    {if $comments}
        {foreach from=$comments item=comment}
            {$comment->text|safe}
            <div class="verifiedon">
                {if $displayverifier}
                    {str tag=verifiedonby section=blocktype.verification arg1=$comment->profileurl arg2=$comment->displayname arg3=`$comment->postdate|format_date`}
                {else}
                    {str tag=verifiedon section=blocktype.verification arg1=`$comment->postdate|format_date`}
                {/if}
            </div>
        {/foreach}
    {/if}
