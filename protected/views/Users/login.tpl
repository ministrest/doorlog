{extends "protected/views/index.tpl"}
    {block name="pagetitle"}<h1>Вход в систему</h1>{/block}

    {block name="content"}
            <div id="auth">
                <form method="POST" action=".">
                    <p>Введите email или номер пропуска:</p> <input type="text" maxlength="40" size="40" name="login"></p>
                    <p>Введите пароль:</p> <input type="password" maxlength="25" size="40" name="password"></p>

                    <input type="hidden" value="auth" name="a">
                    <input type="submit" value="Login" name="submit" class="btn btn-success">
                </form>
            </div>

    {/block}
{/extends}
