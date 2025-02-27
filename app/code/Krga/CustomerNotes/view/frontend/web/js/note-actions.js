require(['jquery', 'Magento_Ui/js/modal/confirm'], function ($, confirm) {
    $(document).ready(function () {

        handleAddNoteFormOpen();

        $('.restore-note').on('click', function (e) {
            e.preventDefault();
            let url = $(this).data('url');
            confirm({
                title: 'Restore Note',
                content: 'Are you sure you want to restore this note?',
                actions: {
                    confirm: function () {
                        window.location.href = url;
                    },
                    cancel: function () {}
                }
            });
        });

        $('.trash-note').on('click', function (e) {
            e.preventDefault();
            let url = $(this).data('url');
            confirm({
                title: 'Move to Trash',
                content: 'Are you sure you want to move this note to trash?',
                actions: {
                    confirm: function () {
                        window.location.href = url;
                    },
                    cancel: function () {}
                }
            });
        });
        
        $('.delete-note').on('click', function (e) {
            e.preventDefault();
            let url = $(this).data('url');
            confirm({
                title: 'Delete Permanently?',
                content: 'Are you sure you want to permanetly delete this note? This action can not be undone!',
                actions: {
                    confirm: function () {
                        window.location.href = url;
                    },
                    cancel: function () {}
                }
            });
        });
        
        $('.revert-note').on('click', function (e) {
            e.preventDefault();
            let url = $(this).data('url');
            confirm({
                title: 'Revert Note?',
                content: 'Are you sure you want to revert note to this version?',
                actions: {
                    confirm: function () {
                        window.location.href = url;
                    },
                    cancel: function () {}
                }
            });
        });
    });

    const handleAddNoteFormOpen = () => {
        const buttonTrigger = $('.add-note-form-trigger'),
              form = $('#add-note-form');

        if (buttonTrigger.length && form.length) {
            buttonTrigger.each(function(){
                const thisButton = $(this);

                thisButton.on('click', function() {
                    form.slideToggle();
                })
            })
        }
    }
});
