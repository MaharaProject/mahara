    <div class="blockinstance-header">
        <div class="blockinstance-controls">
            <input type="image" src="{theme_url filename=images/btn_close.png}" class="deletebutton" name="action_removeimagebrowser" alt="{str tag=Close}">
        </div>
        <h2 class="title">{$title|default:"[$strnotitle]"}</h2>
        <p class="description">{$description|default:""}</p>
    </div>
    <div class="blockinstance-content">
        {$content|safe}
    </div>
    <div id="filebrowserupdatetarget"></div>