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

$shortoptions = 'd:G:g:';
$longoptions = array('days=', 'group-id=', 'group=');

$helptext = <<<END_OF_USERROLE_HELP
updategradesfile.php [options]
updates the file in which the average grade of each student is kept

  -d --days number of d last days
  -G --group-id    ID of group
  -g --group 	nickname or alias of group

END_OF_USERROLE_HELP;

require_once INSTALLDIR . '/scripts/commandline.inc';

// Cogemos número de días de parámetro

if (have_option('d', 'days')) {
    $dayOpt = get_option_value('d', 'days');
} else {
    print "You must provide a number of days.\n";
    exit(1);
}

// Comprobamos que se ha introducido un número correcto
/*if(gettype($dayOpt)!="integer") {
	echo gettype($dayOpt);
    print "You mast provide a number of days (integer), not any other type of variable.\n";
    exit(1);
}*/

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
    print "You must provide the ID or nickname of the group.\n";
    exit(1);
}

$robocodeActivated = Robocodegroups::getIsActivated($gid);
    if ($robocodeActivated == -1) {
	print "Este grupo no tiene activado el plugin Robocode.\n";
	exit(1);
}


$segundos = $dayOpt*86400; // Necesario pasar los días a segundos.
$time = time() - $segundos; // Se le resta a la fecha actual los dias calculados en segundos.
$time = round($time);
$time = gmdate("Y-m-d H:i:s",$time);

$nicks = array();
$nicks = Grades::getMembersNicksExcludeGradersAndAdmin($gid);
GetscoresAction::exportCSV($nicks, $gid, $time);

?>
