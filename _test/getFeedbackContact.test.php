<?php
/**
 * General tests for the feedback plugin
 *
 * @group plugin_feedback
 * @group plugins
 */
class getFeedbackContact_plugin_feedback_test extends DokuWikiTest {

    protected $pluginsEnabled = array('feedback', 'translation');

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $fn = DOKU_CONF . 'plugin_feedback.conf';

        $text = "en	ignore@example.com\nplugin	ignoreB@example.com\n";

        file_put_contents($fn, $text);
    }

    /**
     * @inheritDoc
     */
    public function setUp() {
        parent::setUp();
    }


    public static function getFeedbackContact_testdata() {
        return array(
            array(
                'qwe',
                array(),
                false,
                'return false for page w/o set contact'
            ),
            array(
                'en:plugin:bar',
                array(),
                'ignore@example.com',
                'return contact for page in configured namespace'
            ),
            array(
                'fr:plugin:bar',
                array(),
                false,
                'no contact in translated ns without activated span_translations option'
            ),
            array(
                'fr:plugin:bar',
                array('span_translations' => 1),
                'ignoreB@example.com',
                'return contact in translated ns with activated span_translations option'
            ),
            array(
                'plugin',
                array(),
                false,
                'return false for outside start-page without activated include_parent_startpage option'
            ),
            array(
                'plugin',
                array('include_parent_startpage' => 1),
                'ignoreB@example.com',
                'return contact for outside start-page with activated include_parent_startpage option'
            ),

        );
    }


    /**
     * @dataProvider getFeedbackContact_testdata
     */
    public function test_getFeedbackContact ($input, $options, $expected_output, $message) {
        /** @var action_plugin_feedback $action */
        $action = plugin_load('action', 'feedback');
        global $conf;
        foreach ($options as $key => $value) {
            $conf['plugin']['feedback'][$key] = $value;
        }


        global $conf;
        $conf['plugin']['translation']['translations'] = 'en de fr es';
        $trans = plugin_load('helper', 'translation', true);

        $actual_output = $action->getFeedbackContact($input);

        $this->assertEquals($expected_output, $actual_output, $message);
    }
}
 // getFeedbackContact
