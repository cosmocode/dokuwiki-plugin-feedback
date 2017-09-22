jQuery(function () {
    jQuery('.plugin_feedback')
        .show()
        .click(function (e) {
            e.preventDefault();

            var getPageId = function() {
                //JSINFO avalible in doku.php
                if (JSINFO && JSINFO.id) return JSINFO.id;

                //JSINFO not avalible in detail.php
                var media_id = window.location.href.match(/media=([^&#]*)/)[1];
                return media_id;
            };

            // create the dialog frame
            var $dialog = jQuery(
                '<div>' +
                    '<form>' +
                    '<textarea></textarea>' +
                    '</form>' +
                    '</div>');

            // initialize the dialog functionality
            $dialog.dialog({
                title: LANG.plugins.feedback.title,
                width: 400,
                height: 300,
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
                                    id: getPageId()
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
