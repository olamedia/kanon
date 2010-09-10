<?php
magic::set('title', 'Страница не найдена');
magic::call('html/footer');
?>
<div id="content" class="error_message http_error">
    <h1>
        <i></i>
        Страница не найдена.
    </h1>
    <div>Возможно вы кликнули по старой ссылке или опечатались при наборе адреса.</div>
    <ul>
        <li>
            <a href="/">Вернуться на главную</a>
        </li>
        <li>
            <a href="#" onClick="history.back();">Вернуться на предыдущую страницу</a>
        </li>
    </ul>
</div>
<?php
magic::call('html/footer');
