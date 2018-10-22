<?php

/**
 * 
 * BoloTweet 2.0
 *
 * @authors   	Francisco Javier Pacheco Herranz <franpach@ucm.es>
 *		Javier Romero Pérez <javrom01@ucm.es>	
 */
if (!defined('STATUSNET') && !defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR . '/lib/form.php';

class GetScoreForm extends Form {

    var $pruebaid = null;

    function __construct($out = null) {
        parent::__construct($out);
	if (common_config('attachments', 'uploads')) {
            $this->enctype = 'multipart/form-data';
        }
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */
    function action() {
        return common_local_url('getscores');
    }

    /**
     * Data elements
     *
     * @return void
     */
    function formData() {
	if (common_config('attachments', 'uploads')) {
		$this->out->element('input', array('type' => 'file',
		'name' => 'attach'));
	}
        $this->out->element('input', array('type' => 'submit',
            'value' => 'actualizar resultados', 
            'title' => 'actualiza los resultados de los usuarios'));
    }
}
