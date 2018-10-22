<?php

/**
 * 
 * BoloTweet 2.0
 *
 * @authors   	Francisco Javier Pacheco Herranz <franpach@ucm.es>
 *		Javier Romero Pérez <javrom01@ucm.es>	
 *
 */
if (!defined('STATUSNET')) {
    exit(1);
}

class RobocodePlugin extends Plugin {

    function onInitializePlugin() {
        // A chance to initialize a plugin in a complete environment
    }

    function onCleanupPlugin() {
        // A chance to cleanup a plugin at the end of a program
    }

    function onRouterInitialized($m) {
        $m->connect('main/robocode', array('action' => 'getscores'));
        return true;
    }

    function onCheckSchema() {
        $schema = Schema::get();
	$schema->ensureTable('robocode_scores', Robocode::schemaDef());
	$schema->ensureTable('robocode_groups', Robocodegroups::schemaDef());
        return true;
    }

    function onEndToolsLocalNav($action) {

        $actionName = $action->trimmed('action');

        $user = common_current_user();
        if (!empty($user)) {

	    if ($actionName == 'getscores')
                $action->elementStart('li', array('id' => 'nav_robocode', 'class' => 'current'));
            else
                $action->elementStart('li', array('id' => 'nav_robocode'));

            $action->elementStart('a', array('href' => common_local_url('getscores'), 'title' => _m('Robocode scores')));
            $action->text('Robocode');

            $action->elementEnd('a');
            $action->elementEnd('li');
        }

        return true;
    }

    function onAutoload($cls) {
        $dir = dirname(__FILE__);
        switch ($cls) {
	    case 'Robocodegroups':
            case 'Robocode':
                include_once $dir . '/classes/' . $cls . '.php';
                return false;
                break;
	    case 'GetScoreForm':
		include_once $dir . '/lib/' . $cls . '.php';
		return false;
		break;
	    case 'GetscoresAction':
		include_once $dir . '/actions/' . $cls . '.php';
		return false;
		break;
            default:
                return true;
        }
    }


    function onPluginVersion(&$versions) {
        $versions[] = array('name' => 'RobocodePlugin',
            'version' => STATUSNET_VERSION,
            'author' => 'Francisco Javier Pacheco' , 'Javier Romero',
            'rawdescription' =>
            _m('A plugin to keep a track of robocode battles scores'));
        return true;
    }

}
