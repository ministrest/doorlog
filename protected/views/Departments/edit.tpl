{extends "protected/views/index.tpl"}

{block name="content"}
<script>
        $(document).ready(function()
        {
                $("#dialog").dialog({
                    autoOpen: false,
                    modal: true,
                    position: ["center"],
                    buttons: {
                        "Ок": function() {
                            $("#del-department").submit();
                        },
                        "Отмена": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            $("#delete").click(function(e){
                e.preventDefault();
                $('#dialog').dialog('open');
            });
        });
</script>
    <div class="span7">
        <h1>Редактировать отдел</h1>
        <form method='post' id="edit-department">
            <input type="text" name="depName" value="{$departments['name']}">
        </form>
        
        <form action = "/departments/delete" method='post' id="del-department">
            <input type="hidden" name="id" value="{$departments['id']}">
        </form>

        <button type="submit" class="btn btn-success" form="edit-department"> Сохранить </button>
        <a class="btn" href="{$_root}/departments"> Отмена </a>
        <button type="submit" class="btn btn-danger" id="delete" form="del-department"> Удалить </button>

    </div>
<div id="dialog">
    <p>Дейсвительно хотите удалить?</p>
</div>



{/block}

{/extends}