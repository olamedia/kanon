<?php
magic::set('title', 'На сервере возникла ошибка, и он не может выполнить запрос');
magic::call('html/footer');
?>
<div id="content" class="error_message http_error">
    <h1>
        <i></i>
        На сервере возникла ошибка, и он не может выполнить запрос.
    </h1>
    <ul>
        <li>
            <a href="#" onClick="location.reload(true);">Обновить страницу</a>
        </li>
    </ul>
</div>
<?php
var_dump($_SERVER);
throw new Exception();
magic::call('html/footer');
