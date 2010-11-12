<form action="." method="post" class="box">
	
	<input type="hidden" name="m" value="auth"/>
	
	<table class="g">
	<tr>
		<td><label for="username">E-mail</label></td>
		<td colspan="2"><label for="password">Пароль</label></td>
	</tr>
	<tr>
		<td><input type="text" class="text" id="username" name="user" value="{$user}"></td>
		<td><input type="password" class="text" id="password" name="pwd"></td>
		<td><input type="submit" value="OK"></td>
	</tr>
	</table>
	
</form>