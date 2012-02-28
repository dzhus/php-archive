<?php

class userbox
{
   function run()
   {
      global $user,$skin,$std;
      if (!$user->name) $user->name=$std->input['name'];
      if (
      (($user->uid) and ($std->input['opcode']!='logout')) 
      or
      ($std->input['opcode'] == 'login')
      ) 
      
      $this->html.=$skin->authorized($user->name); else $this->html.=$skin->not_authorized();
      
      
      if ($user->is_admin) 
      {
         
	 $this->html.=$skin->add_entry_link();
         $this->html.=$skin->acp_link();
	 $this->html.=$skin->files_link_admin();
	 $this->html.=$skin->profile_link(1);
      }
	else
      if ($user->uid)
      { 
      	$this->html.=$skin->files_link();
	$this->html.=$skin->profile_link($user->uid);
      }
      if (!$user->uid) $this->html.=$skin->reg_link();
      $this->html.=$skin->sbox_link();

      $this->html=$skin->module($this->html);
   }
}

?>
