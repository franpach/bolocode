<?php

/**
 * 
 * BoloTweet 2.0
 *
 * @authors   	Francisco Javier Pacheco Herranz <franpach@ucm.es>
 *		Javier Romero Pérez <javrom01@ucm.es>	
 */
if (!defined('STATUSNET')) {
    exit(1);
}

require_once INSTALLDIR . '/local/plugins/Grades/classes/Grades.php';

class GetscoresAction extends Action {

    var $user = null;
    var $gids = null;

    /**
     * Take arguments for running
     *
     * This method is called first, and it lets the action class get
     * all its arguments and validate them. It's also the time 
     * to fetch any relevant data from the database.
     *
     * Action classes should run parent::prepare($args) as the first
     * line of this method to make sure the default argument-processing
     * happens.
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    function prepare($args) {
        parent::prepare($args);

        $this->user = common_current_user();
	   $this->gids = Robocode::getRobocodeGroupsByUser($this->user->nickname); # grupos a los que pertenece el usuario
	   if(empty($this->gids)) { // si es empty, o no está en ningún grupo o es grader de uno/varios
		$this->gids = Gradesgroup::getGroups($this->user->id); // gids tendrá grupos en caso de ser grader 
	   }
        return true;
    }

    /**
     * Class handler.
     *
     * @param array $args query arguments
     *
     * @return void
     */
    function handle($args) {

        parent::handle($args);
        if (!common_logged_in()) {
            $this->clientError(_('Not logged in.'));
            return;
        }
	   $this->showPage();
    } 

    function exportCSV($nicks, $groupid, $date) {
	$arrayFinal = array();
	$robotLine = "";
	foreach($nicks as $nick) {
		$media = Grades::devolverUltimosGrades(array('nickname' => $nick, 'groupid' => $groupid, 'date' => $date));
		if(is_null($media)) {
			$media=0;
		}
		$robotLine = $nick.",".$media;
		$arrayFinal[] = $robotLine;
		$robotLine="";
	}
	$filename = "/tmp/g".$groupid."_grades.csv";
	$f = fopen($filename, "w");		
	foreach ($arrayFinal as $line) {
		fputcsv($f, explode(',', $line));
	}
	fclose($f);
    }

    function actScoresByRobocodeFile($groupid, $ficherocsv) {
	if (($f = fopen($ficherocsv, "r")) !== FALSE) {
		while (($datos = fgetcsv($f, 0, ",")) !== FALSE) {
			$length = strlen($datos[0]);
			$nombre = substr($datos[0], 0, $length - 1); // el nombre se guarda con un * que hay que eliminar
			$puntuacion = $datos[1];
			$rondasGanadas = $datos[2];
			Robocode::actualizeScoresByUserAndGroup(array('userid' => $nombre, 'score' => $puntuacion, 'wins' => $rondasGanadas, 'groupid' => $groupid));
		}
	fclose($f);
	}
    }

   
    function showContent() {
	if (empty($this->gids)) {
		$this->element('p', null, 'Lo sentimos, no perteneces a ningún grupo que tenga implementado Robocode');
	}
	foreach($this->gids as $g) {
		$lgroup = Local_group::staticGet('group_id', $g); # sacamos el alias del grupo dado su id
		$this->element('h3', null, 'Clasificación del grupo: ' . $lgroup->nickname); 
	    	$users = Robocode::getAllUsersFromGroup($g);
		$this->elementStart('table',array('class' => 'tg'));
		$this->elementStart('tr');    
		$this->element('th', 'tg-us36', 'Usuario' );
		$this->element('th', 'tg-us36', 'Puntos Totales' );
		$this->element('th', 'tg-us36', 'Rondas Ganadas' );
		$this->elementEnd('tr');
		foreach($users as $u) {
			$scores= Robocode::getScoresByUserAndGroup($u, $g);
			$this->elementStart('tr');
			$this->element('th', 'tg-us36', $u);
			$this->element('th', 'tg-us36', $scores[score]);
			$this->element('th', 'tg-us36', $scores[wins]);
			$this->elementEnd('tr');     
		}
		$this->elementEnd('table');	
		$this->element('br');
	}  

    }
    
    function title() {
        return _m('Robocode scores');
    }

}
