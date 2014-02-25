<?php
/**
 * DokuWiki Plugin feedback (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_feedback extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('FIXME', 'FIXME', $this, 'handle_fixme');
   
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_fixme(Doku_Event &$event, $param) {
    }


    public function tpl(){
        global $ID;
        // fixme check for feedback user

        $info = array(
            'id'   => $ID,
            'user' => $_SERVER['REMOTE_USER'],
            'ua'   => $_SERVER['HTTP_USER_AGENT'],
            'ip'   => clientIP(),
        );
        $json = new JSON(JSON_LOOSE_TYPE);
        $info = $json->encode($info);


        echo '<a href="#" class="plugin_feedback" data-feedback="'.hsc($info).'">'.$this->getLang('feedback').'</a>';
    }

}

// vim:ts=4:sw=4:et:
