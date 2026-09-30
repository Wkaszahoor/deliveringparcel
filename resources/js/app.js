import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/**
 * Auto-init helper for jQuery plugins already loaded by the host layout
 * (select2, summernote, DataTables) — migrated pages add a data attribute
 * instead of repeating $(...).plugin() boilerplate in an inline <script>.
 *   <select data-dp2-select2>              -> select2({ width: '100%' })
 *   <textarea data-dp2-summernote>         -> summernote({ height: 280 })
 *   <table data-dp2-datatable>             -> DataTable({ responsive: true })
 */
function dp2InitPlugins() {
    if (window.jQuery) {
        const $ = window.jQuery;
        if ($.fn.select2) {
            $('[data-dp2-select2]').each(function () {
                $(this).select2({ width: '100%' });
            });
        }
        if ($.fn.summernote) {
            $('[data-dp2-summernote]').each(function () {
                $(this).summernote({
                    height: 280,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']],
                    ],
                });
            });
        }
        if ($.fn.DataTable) {
            $('[data-dp2-datatable]').each(function () {
                $(this).DataTable({ responsive: true, autoWidth: false });
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', dp2InitPlugins);
