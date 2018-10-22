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

$shortoptions = 'G:';
$longoptions = array('group-id=');

$helptext = <<<END_OF_USERROLE_HELP
listGroup.php [options]
returns users of a certain group

  -G --group-id    ID of group

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
    $robocodeActivated = Robocodegroups::getIsActivated($gid);
    if ($robocodeActivated == -1) {
	print "Este grupo no tiene activado el plugin Robocode.\n";
	exit(1);
    }
} else {
    print "You must provide the ID of the group.\n";
    exit(1);
}


$nicks = array();
$nicks = Grades::getMembersNicksExcludeGradersAndAdmin($gid);
foreach($nicks as $n) {
	echo $n." ";
}
?>
