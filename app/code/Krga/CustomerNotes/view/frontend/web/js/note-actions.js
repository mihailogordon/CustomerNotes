require(['jquery', 'Magento_Ui/js/modal/confirm'], function ($, confirm) {
    $(document).ready(function () {

        // Handle Restore Confirmation
        $('.restore-note').on('click', function (e) {
            e.preventDefault(); // Prevent immediate navigation
            let url = $(this).data('url'); // Get the restore URL
            confirm({
                title: 'Restore Note',
                content: 'Are you sure you want to restore this note?',
                actions: {
                    confirm: function () {
                        window.location.href = url; // Redirect if confirmed
                    },
                    cancel: function () {}
                }
            });
        });

        // Handle Move to Trash Confirmation
        $('.trash-note').on('click', function (e) {
            e.preventDefault(); // Prevent immediate navigation
            let url = $(this).data('url'); // Get the trash URL
            confirm({
                title: 'Move to Trash',
                content: 'Are you sure you want to move this note to trash?',
                actions: {
                    confirm: function () {
                        window.location.href = url; // Redirect if confirmed
                    },
                    cancel: function () {}
                }
            });
        });
        
        // Handle Permanent Delete Confirmation
        $('.delete-note').on('click', function (e) {
            e.preventDefault(); // Prevent immediate navigation
            let url = $(this).data('url'); // Get the trash URL
            confirm({
                title: 'Delete Permanently?',
                content: 'Are you sure you want to permanetly delete this note? This action can not be undone!',
                actions: {
                    confirm: function () {
                        window.location.href = url; // Redirect if confirmed
                    },
                    cancel: function () {}
                }
            });
        });
        
        // Handle Revert Confirmation
        $('.revert-note').on('click', function (e) {
            e.preventDefault(); // Prevent immediate navigation
            let url = $(this).data('url'); // Get the trash URL
            confirm({
                title: 'Revert Note?',
                content: 'Are you sure you want to revert note to this version?',
                actions: {
                    confirm: function () {
                        window.location.href = url; // Redirect if confirmed
                    },
                    cancel: function () {}
                }
            });
        });

        // Handle Permanent Delete Confirmation
        $('.delete-history').on('click', function (e) {
            e.preventDefault(); // Prevent immediate navigation
            let url = $(this).data('url'); // Get the trash URL
            confirm({
                title: 'Delete Permanently?',
                content: 'Are you sure you want to permanetly delete this history? This action can not be undone!',
                actions: {
                    confirm: function () {
                        window.location.href = url; // Redirect if confirmed
                    },
                    cancel: function () {}
                }
            });
        });
    });
});
