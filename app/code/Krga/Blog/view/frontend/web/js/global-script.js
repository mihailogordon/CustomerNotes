require(['jquery'], function($) {
    'use strict';

    $(document).ready(function() {
        initCommentReply();
    });

    function initCommentReply() {
        var replyButtons = $('.comment-reply');

        if (replyButtons.length) {
            replyButtons.each(function() {
                var $thisButton = $(this);

                $thisButton.on('click', function(e) {
                    e.preventDefault();

                    var $commentForm = $thisButton.siblings('.post-comment-form');

                    if ($commentForm.length) {
                        $('.post-comment-form').not($commentForm).slideUp();
                        $commentForm.slideDown();
                        var $cancelReply = $commentForm.find('.comment-reply-cancel');

                        if ($cancelReply.length) {
                            $cancelReply.off('click').on('click', function(e) {
                                e.preventDefault();
                                $commentForm.slideUp();
                            });
                        }
                    }
                });
            });
        }
    }
});