function add() {
    res.val(parseInt(num1.val(), 10) + parseInt(num2.val(), 10));
    tries.val(parseInt(tries.val())+1);
}

jQuery(function() {
    num1 = jQuery('input[name="advfrm-Number1"]');
    num2 = jQuery('input[name="advfrm-Number2"]');
    res = jQuery('input[name="advfrm-Result"]');
    tries = jQuery('input[name="advfrm-Tries"]');
    num1.change(add);
    num2.change(add);
});