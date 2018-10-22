<?php

/**
 * 
 * BoloTweet 2.0
 *
 * @author   Alvaro Ortego <alvorteg@ucm.es>
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
class Grades extends Managed_DataObject {

    /**
     * Notice to favor
     */
    public $__table = 'grades';
    public $id;
    public $noticeid = null; // graded notice
    public $userid = null; // user who created the grade
    public $cdate = null; // date where the grade was created
    public $grade = 0; // default grade

    function staticGet($k, $v = null) {
        return Memcached_DataObject::staticGet('Grades', $k, $v);
    }

    function pkeyGet($kv) {
        return Memcached_DataObject::pkeyGet('Grades', $kv);
    }

    /**
     * Data definition for email reminders
     */
    public static function schemaDef() {
        return array(
            'description' => 'Grade notices',
            'fields' => array(
                'noticeid' => array(
                    'type' => 'int',
                    'not null' => true,
                    'description' => 'ID of the notice'
                ),
                'userid' => array(
                    'type' => 'varchar',
                    'not null' => true,
                    'length' => 255,
                    'description' => 'ID del usuario'
                ),
                'grade' => array(
                    'type' => 'int',
                    'not null' => true,
                    'description' => 'Puntuation given'
                ),
                'id' => array(
                    'type' => 'serial',
                    'not null' => true,
                    'description' => 'Puntuation ID'
                ),
                'cdate' => array(
                    'type' => 'timestamp',
                    'not null' => true,
                    'description' => 'Date and time the puntuation was sent'
                ),
            ),
            'primary key' => array('id'),
        );
    }

    static function getIDsGroupsWithGrades() {
        $grade = new Grades();

        $qry = 'SELECT gi.group_id as groupsIDs' .
                ' FROM grades, group_inbox gi WHERE ' .
                ' gi.notice_id = grades.noticeid' .
                ' group by gi.group_id';


        $grade->query($qry); // all select fields will
        // be written to fields of the Grade object. It is required that
        // select fields are named after the Grade fields.

        $foundgroups = array();

        while ($grade->fetch()) {
            $foundgroups[] = $grade->groupsIDs;
        }

        $grade->free();

        return $foundgroups;
    }

    static function getGroupsWithGrades() {
        $grade = new Grades();

        $qry = 'SELECT gi.group_id as groupsIDs' .
                ' FROM grades, group_inbox gi WHERE ' .
                ' gi.notice_id = grades.noticeid' .
                ' group by gi.group_id';


        $grade->query($qry); // all select fields will
        // be written to fields of the Grade object. It is required that
        // select fields are named after the Grade fields.

        $foundgroups = array();

        while ($grade->fetch()) {
            $foundgroups[] = $grade->groupsIDs;
        }

        $grade->free();

        return User_group::multiGet('id', $foundgroups);
    }

    static function getGradedNoticesAndUsersWithinGroup($groupID) {
        $grade = new Grades();
        if (common_config('db', 'quote_identifiers')) {
            $user_table = '"grades"';
        } else {
            $user_table = 'grades';
        }

        $qry = 'select tmp.nickname as userid, sum(tmp.gradeAvg) as grade' .
                ' from (select p.nickname, avg(g.grade) as gradeAvg' .
                ' from grades g, group_inbox gr, notice n, profile p' .
                ' where g.noticeid = gr.notice_id' .
                ' and gr.group_id = ' . $groupID .
                ' and g.noticeid = n.id ' .
                ' and n.profile_id = p.id' .
                ' group by g.noticeid) as tmp' .
                ' group by tmp.nickname order by grade desc';


        $grade->query($qry); // all select fields will
        // be written to fields of the Grade object. It is required that
        // select fields are named after the Grade fields.

        $obtainedgrade = array();

        while ($grade->fetch()) {
            $obtainedgrade[$grade->userid] = $grade->grade;
        }

        $grade->free();
        return $obtainedgrade;
    }

    static function getNoticeGrade($noticeid, $nickname) {

        $grade = new Grades();

        $qry = 'SELECT g.grade ' .
                'FROM grades g ' .
                'WHERE g.noticeid = ' . $noticeid .
                ' AND g.userid = "' . $nickname . '"';

        $grade->query($qry);

        $obtainedgrade = null;

        if ($grade->fetch()) {
            $obtainedgrade = $grade->grade;
        } else {
            $obtainedgrade = '?';
        }

        $grade->free();
        return $obtainedgrade;
    }

    static function getValidGrader($noticeid, $userid) {


        $grade = new Grades();

        $qry = 'SELECT gg.userid ' .
                'FROM grades_group gg, group_inbox gi ' .
                'WHERE gi.notice_id = ' . $noticeid .
                ' AND gi.group_id = gg.groupid' .
                ' AND gg.userid = "' . $userid . '"';

        $grade->query($qry);

        if ($grade->fetch()) {
            $result = true;
        } else {
            $result = false;
        }

        $grade->free();
        return $result;
    }

    static function getNoticeGradesAndGraders($noticeid) {

        $grade = new Grades();
        if (common_config('db', 'quote_identifiers'))
            $user_table = '"grades"';
        else
            $user_table = 'grades';

        $qry = 'SELECT grade as grade, userid as nickname ' .
                'FROM ' . $user_table . ' ' .
                'WHERE grades.noticeid = %d order by grades.cdate DESC';

        // print sprintf($qry, $noticeid);

        $grade->query(sprintf($qry, $noticeid));

        while ($grade->fetch()) {
            $obtainedgrade[$grade->nickname] = $grade->grade;
        }

        if (empty($obtainedgrade)) {
            $obtainedgrade = '?';
        }

        $grade->free();
        return $obtainedgrade;
    }

    static function getNoticeFromUserInGroup($userid, $groupid) {

        $grade = new Grades();

        $qry = 'select distinct noticeid '
                . 'from grades g, group_inbox gi, notice n '
                . 'where g.noticeid = gi.notice_id '
                . 'and gi.group_id = ' . $groupid
                . ' and g.noticeid = n.id '
                . ' and n.profile_id = ' . $userid
                . ' order by n.created desc';

        $grade->query(sprintf($qry, $noticeid));


        while ($grade->fetch()) {
            $ids[] = $grade->noticeid;
        }

        $grade->free();

        return $ids;
    }

    static function getNoticeGradeUserId($noticeid) {

        $grade = new Grades();
        if (common_config('db', 'quote_identifiers')) {
            $user_table = '"grades"';
        } else {
            $user_table = 'grades';
        }

        $qry = 'SELECT grade, userid ' .
                'FROM ' . $user_table . ' ' .
                'WHERE grades.noticeid = %d order by grades.cdate DESC';

        $grade->query(sprintf($qry, $noticeid));

        $obtainedgrade = null;

        if ($grade->fetch()) {
            $obtainedgrade = $grade->userid;
        } else {
            $obtainedgrade = '?';
        }

//print $obtainedgrades->length();
        $grade->free();
        return $obtainedgrade;
    }

    static function register($fields) {

        // MAGICALLY put fields into current scope

        extract($fields);


        $ngrade = new Grades();

        $ngrade->userid = $userid;
        $ngrade->cdate = common_sql_now();
        $ngrade->grade = $grade;
        $ngrade->noticeid = $noticeid;

        $result = $ngrade->insert();

        if (!$result) {
            common_log_db_error($user, 'INSERT', __FILE__);
            return false;
        }

        return $ngrade;
    }

    static function updateNotice($fields) {

        // MAGICALLY put fields into current scope

        extract($fields);

        $gradeBD = new Grades();


        if (common_config('db', 'quote_identifiers')) {
            $user_table = '"grades"';
        } else {
            $user_table = 'grades';
        }

        $time = common_sql_now();

        $qry = 'UPDATE ' . $user_table .
                ' SET grade=' . $grade .
                ', cdate=\'' . $time . '\'' .
                ' WHERE noticeid=' . $noticeid .
                ' AND userid="' . $userid . '"';

        $result = $gradeBD->query($qry);

        if (!$result) {
            common_log_db_error($user, 'UPDATE', __FILE__);
        }

        $gradeBD->free();
    }

    static function getMembersNicksExcludeGradersAndAdmin($groupid) {

        $grade = new Grades();

        $qry = 'select p.nickname as nick '
                . 'from group_member gm, profile p '
                . 'where gm.is_admin <> 1 '
                . 'and gm.group_id = ' . $groupid
                . ' and gm.profile_id = p.id '
                . 'and gm.profile_id not in '
                . '(select gg.userid '
                . 'from grades_group gg '
                . 'where gg.groupid = ' . $groupid . ')';

        $grade->query($qry);

        $nicks = array();

        while ($grade->fetch()) {
            $nicks[] = $grade->nick;
        }

        $grade->free();

        return $nicks;
    }

    static function getMembersExcludeGradersAndAdmin($groupid) {

        $grade = new Grades();

        $qry = 'select gm.profile_id as id '
                . 'from group_member gm '
                . 'where gm.is_admin <> 1 '
                . 'and gm.group_id = ' . $groupid
                . ' and gm.profile_id not in '
                . '(select gg.userid '
                . 'from grades_group gg '
                . 'where gg.groupid = ' . $groupid . ')';

        $grade->query($qry);

        $ids = array();

        while ($grade->fetch()) {
            $ids[] = $grade->id;
        }

        $grade->free();

        return $ids;
    }

    /** Métodos para estadísticas */
    static function getNumberTweetsOfUserInGroup($userid, $groupid) {

        $grade = new Grades();

        $qry = 'select count(gi.notice_id) as number'
                . ' from group_inbox gi, notice n '
                . 'where gi.notice_id = n.id '
                . 'and gi.group_id = ' . $groupid
                . ' and n.profile_id = ' . $userid;

        $grade->query($qry);


        if ($grade->fetch()) {
            $numberTweets = $grade->number;
        }

        $grade->free();

        return $numberTweets;
    }

    static function getNotaMediaYTotalofUserinGroup($userid, $groupid) {

        $grade = new Grades();

        $qry = 'select avg(tmp.gradeAvg) as media, sum(tmp.gradeAvg) as total '
                . 'from (select avg(g.grade) as gradeAvg '
                . 'from grades g, group_inbox gr, notice n '
                . 'where g.noticeid = gr.notice_id '
                . 'and gr.group_id = ' . $groupid
                . ' and g.noticeid = n.id '
                . 'and n.profile_id = ' . $userid
                . ' group by g.noticeid) as tmp';

        $grade->query($qry);


        $notas = array();

        if ($grade->fetch()) {
            $notas[$grade->total] = $grade->media;
        }

        $grade->free();

        return $notas;
    }

    /** Otras funciones */
    static function devolverGrade($resultGrade, $type = "mean") {

        if (!is_array($resultGrade) && $resultGrade == '?') {
            $grade = $resultGrade;
        } else if (is_array($resultGrade) && count($resultGrade) > 1) {

            switch ($type) {
                case 'mean':
                    $count = count($resultGrade);
                    $sum = array_sum($resultGrade);
                    $total = $sum / $count;
                    break;
                case 'median':
                    rsort($resultGrade);
                    $middle = round(count($resultGrade) / 2);
                    $total = $resultGrade[$middle - 1];
                    break;
            }

            $grade = array("Nota" => number_format($total, 2));
        } else if (is_array($resultGrade) && count($resultGrade) == 1) {

            $grade = $resultGrade;
        }

        return $grade;
    } 

    static function devolverUltimosGrades($fields) {
	extract($fields);
	$grade = new Grades();

	$qry = 'select avg(g.grade) as media '
		. 'from grades g, group_inbox gi, notice n, profile p, local_group lg '
		. 'where p.nickname = "' . $nickname . '"'
		. ' and lg.group_id = "' . $groupid . '"'
		. ' and lg.group_id = gi.group_id '
		. ' and n.id = g.noticeid'
		. ' and g.noticeid = gi.notice_id'
		. ' and n.profile_id = p.id'
		. ' and g.cdate > "' . $date .'"';

	$grade->query($qry);
	if($grade->fetch()) {
		$media = $grade->media;
	}
	return $media;
    }

}
