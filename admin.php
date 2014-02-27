<?php
/**
 * DokuWiki Plugin feedback (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class admin_plugin_feedback extends DokuWiki_Admin_Plugin {

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort() {
        return 3000;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly() {
        return false;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
        global $INPUT;
        if(!$INPUT->has('data')) return;

        $data = $INPUT->arr('data');

        $conf = '';
        foreach($data as $row){
            $ns = trim($row['ns']);
            if($ns != '*') $ns = cleanID($ns);
            $mail = trim($row['mail']);
            if(!$ns) continue;
            $conf .= "$ns\t$mail\n";
        }

        if(io_saveFile(DOKU_CONF . 'plugin_feedback.conf', $conf)) {
            msg($this->getLang('saved'), 1);
        }
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        global $ID;

        echo $this->locale_xhtml('intro');

        $conf = confToHash(DOKU_CONF . 'plugin_feedback.conf');
        ksort($conf);

        $action = wl($ID, array('do' => 'admin', 'page' => 'feedback'));
        echo '<form action="'.$action.'" method="post">';

        echo '<table class="inline">';
        echo '<tr>';
        echo '<th>'.$this->getLang('namespace').'</th>';
        echo '<th>'.$this->getLang('email').'</th>';
        echo '</tr>';
        $cnt = 0;
        foreach($conf as $key => $val) {
            echo '<tr>';
            echo '<td><input type="text" name="data['.$cnt.'][ns]" value="'.hsc($key).'" class="edit" /></td>';
            echo '<td><input type="text" name="data['.$cnt.'][mail]" value="'.hsc($val).'" class="edit" /></td>';
            echo '</tr>';
            $cnt++;
        }
        echo '<tr>';
        echo '<td><input type="text" name="data['.$cnt.'][ns]" value=""/></td>';
        echo '<td><input type="text" name="data['.$cnt.'][mail]" value=""/></td>';
        echo '</tr>';
        echo '</table>';

        echo '<input type="submit" value="'.$this->getLang('save').'" class="btn">';

        echo '</form>';

    }
}

// vim:ts=4:sw=4:et: