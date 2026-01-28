/**
 * Example script.js with an additional field for the name
 * of the person giving feedback. See README in this folder
 * to understand what to do with it.
 * 
 * This script extends the original script.js of the plugin.
 */

jQuery(function () {

    // return value if defined, empty string otherwise
    function get_variable_safe(value) {
        if (typeof value === 'undefined') {
            return '---';
        }
        return value;
    }

    jQuery('.plugin_feedback')
        .show()
        .click(function (e) {
            e.preventDefault();

            var getPageId = function() {
                if (window.JSINFO.plugins.feedback.isMedia) {
                    return window.JSINFO.plugins.feedback.mediaID;
                }
                return window.JSINFO.id;
            };

            // create the dialog frame
            var $dialog = jQuery(
                '<div>' +
                    '<form>' +
                        // EXTENSION (part 1/3):
                        // insert "name" field: we insert an additional field
                        // which can be found by its name in part 2 (see below)
                        '<input type="text" name="name" placeholder="' +
                            // a placeholder will be displayed if found in lang.php
                            // (here i.e.: $lang['js']['placeholder_name'] = "Name";)
                            get_variable_safe(LANG.plugins.feedback.placeholder_name) +
                        '"; style="width:100%; margin-bottom:8px;">' +
                        '<textarea placeholder="' +
                            // another placeholder for the feedback text area
                            get_variable_safe(LANG.plugins.feedback.placeholder_feedback) +
                        '"; style="width:100%; height:150px;"></textarea>' +
                    '</form>' +
                '</div>'
            );

            // initialize the dialog functionality
            $dialog.dialog({
                title: LANG.plugins.feedback.title,
                width: 600,
                height: 350,
                dialogClass: 'plugin_feedbackdialog',
                buttons: [
                    {
                        text: LANG.plugins.feedback.cancel,
                        click: function () {
                            $dialog.dialog("close");
                        }
                    },
                    {
                        text: LANG.plugins.feedback.submit,
                        click: function () {
                            var self = this;

                            // EXTENSION (part 2/3):
                            // insert "name" field: store its contents in a
                            // variable called "name" for later use (see below)
                            var name = $dialog.find('input[name="name"]').val();
                            var text = $dialog.find('textarea').val();

                            if (!text) return;

                            // switch button set and empty form
                            $dialog.html('');
                            $dialog.dialog('option', 'buttons',
                                [
                                    {
                                        text: LANG.plugins.feedback.close,
                                        click: function () {
                                            $dialog.dialog("close");
                                        }
                                    }
                                ]
                            );

                            // post the data
                            jQuery.post(
                                DOKU_BASE + 'lib/exe/ajax.php',
                                {
                                    call: 'plugin_feedback',
                                    feedback: text,
                                    // EXTENSION (part 3/3):
                                    // insert "name" field: we use the name "FEEDBACK_NAME"
                                    // to POST our new field, since any field starting with 
                                    // "FEEDBACK_" in the POST result can be addressed 
                                    // later on in mail.txt using "@FEEDBACK_...@" 
                                    // (here: "@FEEDBACK_NAME@")
                                    FEEDBACK_NAME: name, // 👈 NEU
                                    id: getPageId(),
                                    media: !!window.JSINFO.plugins.feedback.isMedia
                                },
                                // display thank you message
                                function (result) {
                                    $dialog.html(result);
                                }
                            );
                        }
                    }
                ],
                // remove HTML from DOM again
                close: function () {
                    jQuery(this).remove();
                }
            });
        });
});
