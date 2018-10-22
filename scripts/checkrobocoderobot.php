#!/usr/bin/env php
<?php
/**
 * 
 * BoloTweet 2.0
 *
 * @authors   	Francisco Javier Pacheco Herranz <franpach@ucm.es>
 *		Javier Romero Pérez <javrom01@ucm.es>	
 */

define('INSTALLDIR', realpath(dirname(__FILE__) . '/..'));

$shortoptions = 'n:g:G:';
$longoptions = array('nickname=', 'group=', 'group-id=');

$helptext = <<<END_OF_USERROLE_HELP
updaterobocodescores.php [options]
checks if a user belongs to a group

  -G --group-id    ID of group
  -g --group    nickname or alias of group
  -n --nickname    nickname of the user to check

END_OF_USERROLE_HELP;

require_once INSTALLDIR . '/scripts/commandline.inc';


try {
    $user = getUser();
    $lgroup = null;
    if (have_option('G', 'group-id')) {
        $gid = get_option_value('G', 'group-id');
        $lgroup = Local_group::staticGet('group_id', $gid);
    } else if (have_option('g', 'group')) {
        $gnick = get_option_value('g', 'group');
        $lgroup = Local_group::staticGet('nickname', $gnick);
    }
    if (empty($lgroup)) {
        throw new Exception("No such local group: $gnick");
    }
    $group = User_group::staticGet('id', $lgroup->group_id);
    
    if($user->isMember($group)){
    	print "OK\n";
        
    }
    
    else{
	throw new Exception("'$user->nickname' no pertenece al grupo '$group->nickname'");
    }
} catch (Exception $e) {
    print $e->getMessage()."\n";
    exit(1);
}
?>
