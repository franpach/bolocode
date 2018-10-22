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

$shortoptions = 'G:g:';
$longoptions = array('group-id=', 'group=');

$helptext = <<<END_OF_USERROLE_HELP
getGroupId.php [options]
gets group ID if nickname given or nickname if ID given

  -G --group-id    ID of group
  -g --group 	nickname or alias of group

END_OF_USERROLE_HELP;

require_once INSTALLDIR . '/scripts/commandline.inc';

// Cogemos ID de grupo de parámetro

if (have_option('G', 'group-id')) {
    $gid = get_option_value('G', 'group-id');
    $lgroup = Local_group::staticGet('group_id', $gid);
    if (empty($lgroup)) {
         print "Grupo no existente.\n";
         exit(1);
    }
    echo $lgroup->nickname;
} else if (have_option('g', 'group')) {
    $gnick = get_option_value('g', 'group');
    $lgroup = Local_group::staticGet('nickname', $gnick);
    if (empty($lgroup)) {
         print "Grupo no existente.\n";
	    exit(1);
    }
    $group = User_group::staticGet('id', $lgroup->group_id);
    echo $group->id;
  
} else {
    print "You must provide the ID or nickname of the group.\n";
    exit(1);
}

?>
