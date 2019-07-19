$(function () {

    $('#userPerms .col-md-12:not(.nexting) input:not(#userPermEnableDisable)').each(function(){
        var self = $(this);
        if (self[0].checked) {
            self.closest('.col-md-12').next().slideToggle(200);
        }
    });

    $('#userPerms .col-md-12:not(.nexting) input:not(#userPermEnableDisable)').on('change', function () {
        var self = $(this);
        if (self.closest('.col-md-12').next().hasClass('nexting')) {
            if (self[0].checked) {
                self.closest('.col-md-12').addClass('nexting-open');
                self.closest('.col-md-12').next().find('input').each(function () {
                    if (!this.checked) {
                        $(this).next().trigger('click');
                    }
                });
            } else {
                self.closest('.col-md-12').next().find('input').each(function () {
                    if (this.checked) {
                        $(this).next().trigger('click');
                    }
                });
                self.closest('.col-md-12').removeClass('nexting-open');
            }
            self.closest('.col-md-12').next().slideToggle(200);
        }
    });

    $('#userPermEnableDisable').on('change', function () {
        var self = $(this);
        var std = self[0].checked;
        self.closest('.col-md-12').closest('.row').find('.col-md-12').not(self.closest('.col-md-12')).each(function () {
            if (!$(this).hasClass('nexting')) {
                if (std) {
                    if(!$(this).find('input')[0].checked){
                        $(this).find('label').trigger('click');
                    }
                } else {
                    if($(this).find('input')[0].checked){
                        $(this).find('label').trigger('click');
                    }
                }
            }
        });
    });

});