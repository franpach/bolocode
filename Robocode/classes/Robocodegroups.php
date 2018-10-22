<?php

/**
 * 
 * BoloTweet 2.0
 *
 * @authors   	Francisco Javier Pacheco Herranz <franpach@ucm.es>
 *		Javier Romero Pérez <javrom01@ucm.es>	
 *
 */
if (!defined('STATUSNET') && !defined('LACONICA')) {
    exit(1);
}

/**
 * Form for favoring a notice
 *
 * @category Form
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @author   Sarven Capadisli <csarven@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 *
 * @see      DisfavorForm
 */
class Robocodegroups extends Managed_DataObject {
    public $__table = 'robocode_groups';
    public $groupid = null;
    public $is_activated = null;

    function staticGet($k, $v = null) {
        return Memcached_DataObject::staticGet('Robocode', $k, $v);
    }

    function pkeyGet($kv) {
        return Memcached_DataObject::pkeyGet('Robocode', $kv);
    }

    public static function schemaDef() {
        return array(
            'description' => 'Tabla de puntuaciones de robocode',
            'fields' => array(
		'groupid' => array(
		    'type' => 'int',
		    'not null' => true,
	 	    'description' => 'ID del grupo'
		),
		'is_activated' => array(
    	            'type' => 'int', 
		    'size' => 'tiny', 
		    'default' => 0, 
		    'description' => 'is this group using robocode?'
		),
            ),
            'primary key' => array('groupid'),
        );
    }

    static function newGroup($groupid, $isActivated) {	
	$rgroups = new Robocodegroups();
	$qry = 'INSERT INTO robocode_groups(groupid, is_activated) '
		. 'VALUES(' . $groupid . ', ' . $isActivated . ')';
	$rgroups->query($qry);
    }
   
    static function setIsActivated($fields) {
	extract($fields);
	$rgroups = new Robocodegroups();
	$existe = Robocodegroups::getIsActivated($groupid);
	if ($existe == -1) {
		Robocodegroups::newGroup($groupid, $isActivated);
	}
	else {
		$qry = 'UPDATE robocode_groups '
			. 'SET is_activated = ' . $isActivated 
			. ' WHERE groupid = ' . $groupid;
		$rgroups->query($qry);
	}	
    }
  
    static function getIsActivated($groupid) {
	$rgroups = new Robocodegroups();
	$qry = 'SELECT is_activated '
		. 'FROM robocode_groups '
		. 'WHERE groupid = ' . $groupid;
	$rgroups->query($qry);
	if($rgroups->fetch()) {
		$isActivated = $rgroups->is_activated;
	}
	else {
		$isActivated = -1;
	}
	return $isActivated;
    }

    static function getAllGroupsWithRobocode() {
	$rgroups = new Robocodegroups();
	$qry = 'SELECT groupid '
		. 'FROM robocode_groups '
		. 'WHERE is_activated = 1';
	$rgroups->query($qry);
	$groups = array();
	while($rgroups->fetch()) {
		$groups[] = $rgroups->groupid;
	}
	$rgroups->free();
	return $groups;
    }

    static function eliminarGrupo($groupid) {
	$rgroups = new Robocodegroups();
	$qry = 'DELETE FROM robocode_groups'
		. ' WHERE groupid=' . $groupid;
	$rgroups->query($qry);
    }
}
