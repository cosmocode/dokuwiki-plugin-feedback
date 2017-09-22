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

    /*
     * @var true if we are on the detail page
     */
    protected $detail_page = false;

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax');
        $controller->register_hook('DETAIL_STARTED', 'BEFORE', $this, 'handle_detail_started');
    }

    /**
     * Chceck if we can give a feedback
     *
     * @return bool
     */
    protected function feedback_possible() {
        global $ACT, $ID;

        // only on show and detail pages
        if($ACT != 'show' && !$this->detail_page) return false;
        // allow anonymous feedback?
        if(!$_SERVER['REMOTE_USER'] && !$this->getConf('allowanon')) return false;
        // any contact defined?
        if(!$this->getFeedbackContact($ID)) return false;

        return true;
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_ajax(Doku_Event &$event, $param) {
        // our event?
        if($event->data != 'plugin_feedback') return;
        $event->preventDefault();
        $event->stopPropagation();

        // allow anonymous feedback?
        if(!$_SERVER['REMOTE_USER'] && !$this->getConf('allowanon')) {
            http_status(400);
            die('no anonymous access');
        }

        // get submitted data
        global $INPUT;
        $id = $INPUT->str('id');
        $feedback = $INPUT->str('feedback');
        $media = $INPUT->bool('media');

        // get the responsible contact
        $contact = $this->getFeedbackContact($id);
        if(!$contact) {
            http_status(400);
            die('no contact defined');
        }

        // get info on user
        $user = null;
        if($_SERVER['REMOTE_USER']) {
            /** @var DokuWiki_Auth_Plugin $auth */
            global $auth;
            $user = $auth->getUserData($_SERVER['REMOTE_USER']);
            if(!$user) $user = null;
        }

        // send the mail
        $mailer = new Mailer();
        $mailer->to($contact);
        if($user) $mailer->setHeader('Reply-To', $user['mail']);
        $mailer->subject($this->getLang('subject'));
        if ($media) {
            $url = ml($id, '', false, '&amp;', true);
        } else {
            $url = wl($id, '', true);
        }
        $mailer->setBody(
            io_readFile($this->localFN('mail')),
            array('PAGE' => $id, 'FEEDBACK' => $feedback, 'URL' => $url)
        );
        $success = $mailer->send();
        header('Content-Type: text/html; charset=utf-8');

        if (!$success) {
            echo $this->getLang('error');
            return;
        }

        echo $this->getLang('thanks');
    }

    /**
     * Set the flag when we are on the detail page
     */
    public function handle_detail_started() {
        $this->detail_page = true;
    }

    /**
     * Get the responsible contact for givven ID
     *
     * @param $id
     * @return false|string
     */
    public function getFeedbackContact($id) {
        $conf = confToHash(DOKU_CONF . 'plugin_feedback.conf');

        $ns = $id;

        if ($this->getConf('span_translations')) {
            $ns = $this->adjustForTanslations($id);
        }

        if ($this->getConf('include_parent_startpage')) {
            if(isset($conf[$ns])) {
                return $conf[$ns];
            }
        }

        do {
            $ns = getNS($ns);
            if(!$ns) $ns = '*';
            if(isset($conf[$ns])) return $conf[$ns];
        } while($ns != '*');

        return false;
    }

    /**
     * prints or returns the the action link
     *
     * Alternatively you can add the plugin_feedback class to any object in the DOM and it will be used
     * for triggering the feedback dialog
     *
     * @param bool $return
     * @return string
     */
    public function tpl($return = false) {

        if(!$this->feedback_possible()) return;

        $html = '<a href="#" class="plugin_feedback">' . $this->getLang('feedback') . '</a>';
        if($return) return $html;
        echo $html;
        return '';
    }

    /**
     * If this is a translated page, remove the language-prefix
     *
     * @param string $id
     *
     * @return string
     */
    protected function adjustForTanslations($id) {
        /** @var helper_plugin_translation $trans */
        $trans = plugin_load('helper', 'translation', defined('DOKU_UNITTEST'));
        if ($trans) {
            list(, $id) = $trans->getTransParts($id);
        } else {
            msg('Option span_translations has been activated in feedback-plugin, but translation-plugin is not installed/enabled!', -1);
        }
        return $id;
    }

}

// vim:ts=4:sw=4:et:
