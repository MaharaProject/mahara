    <a class="quickeditlink {if $withdisplay}with-displaylink {/if}modal_link list-group-heading "
        data-bs-toggle="modal-docked"
        data-bs-target="#configureblock"
        href="#"
        data-blockid="{$blockid}"
        title="{str tag=quickedit section=view}">
        <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true" title="{str tag=quickedit section=view}"></span>
        {str tag=quickedit section=view}
    </a>
