    <div class="blockinstance-header">
        <div class="blockinstance-controls">
            <input type="image" src="{theme_image_url filename=btn_close}" class="deletebutton" name="action_removeimagebrowser" alt="{str tag=Close}">
        </div>
        <h2 class="title">{$title|default:"[$strnotitle]"}</h2>
        <p class="description">{$description|default:""}</p>
    </div>
    <div class="blockinstance-content">
        {$content|safe}
    </div>
    <div id="filebrowserupdatetarget"></div>