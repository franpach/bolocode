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

$shortoptions = 'G:a:g:';
$longoptions = array('group-id=', 'activated=', 'group=');

$helptext = <<<END_OF_USERROLE_HELP
updategradesfile.php [options]
activates or deactivates robocode plugin in a group

  -G --group-id    ID of group
  -a --activated   if activated or not
  -g --group       nickname or alias of group

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
} else if (have_option('g', 'group')) {
    $gnick = get_option_value('g', 'group');
    $lgroup = Local_group::staticGet('nickname', $gnick);
    if (empty($lgroup)) {
         print "Grupo no existente.\n";
	    exit(1);
    }
    $group = User_group::staticGet('id', $lgroup->group_id);
    $gid = $group->id;
} else {
    print "You must provide the ID of the group.\n";
    exit(1);
}

// Cogemos activated de parámetro

if (have_option('a', 'activated')) {
    $isActivated = get_option_value('a', 'activated');
    if (($isActivated != "1") && ($isActivated != "0")) {
	print "You can only write 1 (activated) or 0 (deactivated)\n";
	exit(1);
    }
} else {
    print "You must indicate if you want the plugin activated (1) or deactivated (0) (option -a)\n";
    exit(1);
}

Robocodegroups::setIsActivated(array('groupid'=>$gid, 'isActivated'=>$isActivated));

?>
