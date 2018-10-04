var actions = (function($) {
    return {
        conf: {
            addtofavorites_url: '',
            removefromfavorites_url: ''
        },
        
        init: function(settings) {
            $.extend(true, this.conf, settings);
            this.bindActions();
        },
        
        bindActions: function() {
            var that = this;
            $('.addtofavorites').on('click', function(e) {that.addToFavorites(e)});
            $('.removefromfavorites').on('click', function(e) {that.removeFromFavorites(e)});
        },   
        
        addToFavorites: function (e) {
            e.preventDefault();
            var book_id = $(e.target).data('bookid');
            $.ajax({
                    url: this.conf.addtofavorites_url,
                    type:'GET',
                    data: {book_id: book_id},
                    dataType: 'json',                   
                    success:function(response)
                    {
                        if (response.status === 'ok') {  
                            $('#add_' + book_id).hide();
                            $('#remove_' + book_id).show();                                                               
                        } else {
                            console.log(response.status);   
                        }
                    }
            });      
        },
        
        removeFromFavorites: function (e) {
            e.preventDefault();
            var book_id = $(e.target).data('bookid');  
            $.ajax({
                    url: this.conf.removefromfavorites_url,
                    type:'GET',
                    data: {book_id: book_id},
                    dataType: 'json',                   
                    success:function(response)
                    {
                        if (response.status === 'ok') {  
                            if (is_user_page) {
                                location.reload();
                            } else {
                                $('#add_' + book_id).show();
                                $('#remove_' + book_id).hide(); 
                            }
                        } else {
                            console.log(response.status);   
                        }
                    }
            });            
        }        
    };
})(jQuery);

