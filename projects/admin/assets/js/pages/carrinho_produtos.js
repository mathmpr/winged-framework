function addToCart(id_produto){
    $.ajax({
        url: window.protocol + window._parent + 'carrinho/add-to-cart/',
        type: 'post',
        data: {id_produto: id_produto},
        success: function (response) {
        }
    })
}
$(function(){
    $('.add-cart').each(function(){
        bind_add_cart($(this));
    });
});
function bind_add_cart(element){
    element.on('click', function () {
        element.unbind('click');
        var id_produto = element.attr('data-id');
        element.find('i').addClass('icon-spinner11').addClass('infinite-rotation').removeClass('icon-cart-add');
        setTimeout(function(){
            $.ajax({
                url: window.protocol + window._parent + 'carrinho/add-to-cart/',
                type: 'post',
                data: {id_produto: id_produto},
                success: function (response) {
                    response = json(response);
                    if(response){
                        if(response.status){
                            new PNotify({
                                title: 'Sucesso',
                                text: response.message,
                                addclass: 'bg-success'
                            });
                            element.find('i').removeClass('icon-spinner11').removeClass('infinite-rotation').addClass('icon-checkmark');
                            element.addClass('bg-success-400').removeClass('bg-blue-400');
                        }else{
                            new PNotify({
                                title: 'Erro',
                                text: response.message,
                                addclass: 'bg-danger'
                            });
                            element.find('i').removeClass('icon-spinner11').removeClass('infinite-rotation').addClass('icon-cross2');
                            element.addClass('bg-danger-400').removeClass('bg-blue-400');
                        }
                    }else{
                        new PNotify({
                            title: 'Erro',
                            text: 'Erro no servidor. Aguarde e tente novamente.',
                            addclass: 'bg-danger'
                        });
                        element.find('i').removeClass('icon-spinner11').removeClass('infinite-rotation').addClass('icon-cross2');
                        element.addClass('bg-danger-400').removeClass('bg-blue-400');
                    }
                    setTimeout(function(){
                        element.find('i').removeClass('icon-checkmark').removeClass('icon-cross2').addClass('icon-cart-add');
                        element.removeClass('bg-success-400').addClass('bg-blue-400');
                        bind_add_cart(element);
                    }, 1000);
                }
            });
        }, 1000);
    });
}
