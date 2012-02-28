<?php

class skin_auth
{
   
   function module($data)
   {
      return "
      <div class='module'><br /><br /><center>{$data}</center></div>
      ";
   }
   
   function login_form()
   {
      return "
      <form name='login_form' action='index.php' method='post'>
      <input name='name' type='text' value='Имя' onClick=\"this.value='';\"><br />
      <input name='pass' type='password' value='pass' onClick=\"this.value='';\"><br />
      <input name='opcode' type='hidden' value='login'>
      <input name='a' type='hidden' value='auth'>
      <br /><br /><input type='submit' value='Вход'>
      </form>
      ";
   }
   

   
}
$skin = new skin_auth;

?>