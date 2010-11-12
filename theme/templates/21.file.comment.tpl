<form action="." method="post" class="form" id="comment">
	
	<input type="hidden" name="m" value="comment"/>
	<input type="hidden" name="id_file" value="{$id_file}"/>
	<input type="hidden" name="id_parent" value="{$id_parent|default:0}"/>
	
	<p>
		<label for="username">Пользователь</label>
		<input type="text" class="form" id="username" name="username" value="{$author|default:'Анонимус'}"{if $author} disabled="disabled"{/if} size="48"/>
	</p>
	
	<p>
		<label for="username">Комментарий</label>
		<textarea name="text" cols="80" rows="4"></textarea>
	</p>
	
	<p>
		<input type="submit" value="Отправить"/>
	</p>
</form>