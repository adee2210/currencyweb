class exchanger {
    constructor(rate){
        this.rate = rate;

        this.listening();
    }

    listening(){
        var obj = this;
        $('input.from-amount').keyup(function(){
            if(obj.decimal($(this))){
                var rate = `${obj.rate}`;
                $('.lbl-from').html($(this).val()+' '+$('select.from-symbol option:selected').text()+' equal to');
                var conv = (rate*$(this).val()).toFixed(5);
                $('.lbl-to').html(conv+' '+$('select.to-symbol option:selected').text());
                $('input.to-amount').val(conv);
            }
        });
        $('input.to-amount').keyup(function(){
            if(obj.decimal($(this))){
                var rate = `${obj.rate}`;
                $('.lbl-to').html($(this).val()+' '+$('select.to-symbol option:selected').text());
                var conv = (1/rate*$(this).val()).toFixed(5);
                $('.lbl-from').html(conv+' '+$('select.from-symbol option:selected').text()+' equal to');
                $('input.from-amount').val(conv);
            }
        });

        $('select.from-symbol').change(function(){
            var symbol = $(this).val();
            $.post(
                '/request',
                {symbol:symbol},
                function(response){
                    if(typeof(response.data) == 'object'){
                        $('select.to-symbol').html('');
                        $.each(response.data,function(idx,val){
                            $('select.to-symbol').append('<option>'+val+'</option>');
                        });
                        if(response.rate >= 0){
                            obj.rate = response.rate;
                            var conv = (obj.rate*$('input.from-amount').val()).toFixed(5);
                            $('.lbl-from').html($('input.from-amount').val()+' '+symbol+' equals');
                            $('.lbl-to').html(conv+' '+response.data[0]);
                            $('input.to-amount').val(conv);
                        }
                    }
                }, 
                "json"
            );
        });

        $('select.to-symbol').change(function(){
            var symbol = $(this).val();
            $.post(
                '/rate',
                {fromsymbol:$('select.from-symbol option:selected').text(),tosymbol:symbol},
                function(response){
                    if(response.rate >= 0){
                        obj.rate = response.rate;
                        var conv = (obj.rate*$('input.from-amount').val()).toFixed(5);
                        $('.lbl-from').html($('input.from-amount').val()+' '+$('select.from-symbol option:selected').text()+' equal to');
                        $('.lbl-to').html(conv+' '+symbol);
                        $('input.to-amount').val(conv);
                    }
                }, 
                "json"
            );
        });
    }

    decimal(obj){
        var val = obj.val().replace(/[^0-9\.]/,'');
        var dot = val.split(".");
        if(dot.length>2)
            val = dot[0]+"."+dot[1];
        if(val.length>1)
            if(val[0]==0)
                val = val.slice(1);
        if(val.length==0)
            val = 0;
        return obj.val(val);
    }
}