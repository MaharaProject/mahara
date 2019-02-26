{if $r.delete}
    <button type="button" onclick="deleteidp(this, '{$r.idpentityid}')" name="delete" value="{$r.idpentityid}" class="deletebutton btn btn-secondary btn-sm" alt='{str tag=deletespecific section=mahara arg1=$r.name}'>
        <span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span>
        <span class="sr-only">
            {str tag=delete section=mahara}
        </span>
    </button>
{/if}
