
(function(){
    window.Pagination = {
        render: function(container, data, onPageClick) {
            var meta = data;
            // Handle if data is wrapped in 'data' key or is the paginator itself
            if (data.meta) meta = data.meta; 
            else if (data.current_page) meta = data;
            
            if (!meta || !meta.total) {
                $(container).empty();
                return;
            }

            var html = '<div class="d-flex justify-content-between align-items-center mt-3">';
            
            // Info text
            var from = meta.from || 0;
            var to = meta.to || 0;
            html += '<div class="text-muted small">Showing ' + from + ' to ' + to + ' of ' + meta.total + ' entries</div>';
            
            // Pagination
            html += '<nav><ul class="pagination pagination-sm mb-0">';
            
            // Prev
            var prevDisabled = meta.current_page === 1 ? 'disabled' : '';
            html += '<li class="page-item ' + prevDisabled + '"><a class="page-link" href="#" data-page="' + (meta.current_page - 1) + '">Previous</a></li>';
            
            // Pages
            var last = meta.last_page;
            var current = meta.current_page;
            
            // Logic to show a window of pages
            var delta = 2;
            var left = current - delta;
            var right = current + delta + 1;
            var range = [];
            var rangeWithDots = [];
            var l;

            for (let i = 1; i <= last; i++) {
                if (i == 1 || i == last || i >= left && i < right) {
                    range.push(i);
                }
            }

            for (let i of range) {
                if (l) {
                    if (i - l === 2) {
                        rangeWithDots.push(l + 1);
                    } else if (i - l !== 1) {
                        rangeWithDots.push('...');
                    }
                }
                rangeWithDots.push(i);
                l = i;
            }

            rangeWithDots.forEach(function(i){
                if (i === '...') {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                } else {
                    var active = i === current ? 'active' : '';
                    html += '<li class="page-item ' + active + '"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
                }
            });
            
            // Next
            var nextDisabled = meta.current_page === meta.last_page ? 'disabled' : '';
            html += '<li class="page-item ' + nextDisabled + '"><a class="page-link" href="#" data-page="' + (meta.current_page + 1) + '">Next</a></li>';
            
            html += '</ul></nav>';
            html += '</div>';
            
            $(container).html(html);
            
            $(container).find('a.page-link').off('click').on('click', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                if (page && page !== meta.current_page && page > 0 && page <= meta.last_page) {
                    onPageClick(page);
                }
            });
        }
    };
})();
