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
class Robocode extends Managed_DataObject {
    public $__table = 'robocode_scores';
    public $id = null; // id
    public $userid = null;
    public $groupid = null;
    public $totalscore = null;
    public $roundswon = null;

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
                'id' => array(
                    'type' => 'serial',
                    'not null' => true,
                    'description' => 'Score ID'
                ),
		'userid' => array(
                    'type' => 'varchar',
                    'not null' => true,
                    'length' => 255,
                    'description' => 'ID del usuario'
                ),
		'totalscore' => array(
                    'type' => 'int',
                    'not null' => true,
                    'description' => 'puntuación total'
                ),
		'roundswon' => array(
                    'type' => 'int',
                    'not null' => true,
                    'description' => 'número de rondas ganadas'
                ),
		'groupid' => array(
		    'type' => 'int',
		    'not null' => true,
	 	    'description' => 'ID del grupo'
		),
            ),
            'primary key' => array('id', 'userid', 'groupid'),
        );
    }

    static function getScoresByUserAndGroup($userid, $groupid) {
	$robocode = new Robocode();
	$qry = 'SELECT totalscore, roundswon '
		. 'FROM robocode_scores '
		. 'WHERE userid= "' . $userid . '"'
		. ' AND groupid= ' . $groupid;

	$robocode->query($qry);
	$obtainedscores = array();

        if ($robocode->fetch()) {
            $obtainedscores[score] = $robocode->totalscore;
            $obtainedscores[wins] = $robocode->roundswon;
        }
	else { // si no está, le ponemos de nombre de robot el del user y sus puntos a 0
	    Robocode::newRobot(array('userid' => $userid, 'groupid' => $groupid));
            $obtainedscores[score] = 0;
            $obtainedscores[wins] = 0;
	}

        return $obtainedscores;
	
    }

    static function newRobot($fields) {
	extract($fields);
	$robocode = new Robocode();
	$qry = 'INSERT INTO robocode_scores (userid, groupid, totalscore, roundswon) '
		. 'VALUES ("' . $userid . '", ' . $groupid . ', 0, 0)';
	$robocode->query($qry);
    }

    static function actualizeScoresByUserAndGroup($fields) { 
	extract($fields);
	$oldscore = Robocode::getScoresByUserAndGroup($userid, $groupid);
	$score = $score + $oldscore[score]; // le sumamos los puntos que ya tenía a los que ha logrado
	$wins = $wins + $oldscore[wins];
	$robocode = new Robocode();
	$qry = 'UPDATE robocode_scores '
		. 'SET totalscore = ' . $score . ', roundswon = ' . $wins . 
		' WHERE userid= "' . $userid . '"'
       	. ' AND groupid = ' . $groupid;
	$robocode->query($qry);
    }  

    static function getAllUsersFromGroup($groupid) {
	$robocode = new Robocode();
	$qry = 'SELECT userid '
		. 'FROM robocode_scores' 
		. ' WHERE groupid= ' . $groupid 
		. ' ORDER BY totalscore DESC';
	$robocode->query($qry);
	$users = array();
	while($robocode->fetch()) {
		$users[] = $robocode->userid;
	}
	$robocode->free();
	return $users;
    }

    static function getRobocodeGroupsByUser($user) {
	$robocode = new Robocode();
	$qry = 'SELECT groupid '
		. 'FROM robocode_scores' 
		. ' WHERE userid= "' . $user . '"';
	$robocode->query($qry);
	$groups = array();
	while($robocode->fetch()) {
		$groups[] = $robocode->groupid;
	}
	$robocode->free();
	return $groups;
    }

    static function eliminarGrupo($groupid) {
	$robocode = new Robocode();
	$qry = 'DELETE FROM robocode_scores'
		. ' WHERE groupid=' . $groupid;
	$robocode->query($qry);
    }

    static function eliminarUser($user) {
	$robocode = new Robocode();
	$qry = 'DELETE FROM robocode_scores'
		. ' WHERE userid= "' . $user . '"';
	$robocode->query($qry);
    }
}
