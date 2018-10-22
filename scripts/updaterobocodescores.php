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

$shortoptions = ':f:G:g:';
$longoptions = array('filename=', 'group-id=', 'group=');

$helptext = <<<END_OF_USERROLE_HELP
updaterobocodescores.php [options]
updates the scores from robocode battles

  -G --group-id    ID of group
  -f --filename    name of the file
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

if (have_option('f', 'filename')) {
    $filename = get_option_value('f', 'filename');
} else {
    print "You must provide the name of the file.\n";
    exit(1);
}

$robocodeActivated = Robocodegroups::getIsActivated($gid);
    if ($robocodeActivated == -1) {
	print "Este grupo no tiene activado el plugin Robocode.\n";
	exit(1);
}

GetscoresAction::actScoresByRobocodeFile($gid, $filename);

?>
