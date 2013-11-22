/*
 * Form editor of Advancedform_XH.
 * Copyright (c) 2005-2010 Jan Kanters
 * Copyright (c) 2011-2013 Christoph M. Becker (see license.txt)
 */


/**
 * Property indexes.
 */
var ADVFRM_PROP_SIZE = 0;
var ADVFRM_PROP_COLS = 0;
var ADVFRM_PROP_MAXLEN = 1;
var ADVFRM_PROP_ROWS = 1;
var ADVFRM_PROP_DEFAULT = 2;
var ADVFRM_PROP_VALUE = 2;
var ADVFRM_PROP_FTYPES = 2;
var ADVFRM_PROP_CONSTRAINT = 3;
var ADVFRM_PROP_ERROR_MSG = 4;


/**
 * Returns whether type designates a selection field (select, checkbox or radio).
 *
 * @param {String}  type
 * @return {boolean}
 */
function advfrm_isSelect(type) {
    return jQuery.inArray(type, ['radio', 'checkbox', 'select', 'multi_select']) > -1;
}


/**
 * Returns whether type designates is a select field.
 *
 * @param {String}  type
 * @return {boolean}
 */
function advfrm_isRealSelect(type) {
    return type == 'select' || type == 'multi_select';
}


/**
 * Returns whether type designates a multi selection field (select multiple or checkbox).
 *
 * @param {String}  type
 * @return {boolean}
 */
function advfrm_isMulti(type) {
    return type == 'checkbox' || type == 'multi_select';
}


/**
 * Returns the property labels for the given type.
 *
 * @param {String} type
 * @return {Array}
 */
function advfrm_propLabels(type) {
    switch (type) {
        case 'custom':
            return ['size', 'maxlen', 'default', 'constraint', 'error_msg'];
        case 'text':
        case 'from_name':
        case 'from':
        case 'mail':
        case 'date':
        case 'number':
        case 'password':
            return ['size', 'maxlen', 'default'];
        case 'file':
            return ['size', 'maxlen', 'ftypes'];
        case 'textarea':
            return ['cols', 'rows', 'default'];
        case 'hidden':
        case 'output':
            return ['value'];
        default:
            return [];
    }
}


/**
 * Returns the visible properties of the given type.
 *
 * @param {String} type
 * @return {Array}
 */
function advfrm_visibleProps(type) {
    switch (type) {
        case 'custom':
            return [ADVFRM_PROP_SIZE, ADVFRM_PROP_MAXLEN, ADVFRM_PROP_DEFAULT, ADVFRM_PROP_CONSTRAINT, ADVFRM_PROP_ERROR_MSG];
        case 'text':
        case 'from_name':
        case 'from':
        case 'mail':
        case 'date':
        case 'number':
        case 'password':
        case 'file':
            return [ADVFRM_PROP_SIZE, ADVFRM_PROP_MAXLEN, ADVFRM_PROP_DEFAULT];
        case 'textarea':
            return [ADVFRM_PROP_COLS, ADVFRM_PROP_ROWS, ADVFRM_PROP_DEFAULT];
        case 'hidden':
        case 'output':
            return [ADVFRM_PROP_VALUE];
        default:
            return [];
    }

}
/**
 * Gets or sets the selected fields properties.
 *
 * @param  {Array}  props
 * @return {mixed}
 */
function advfrm_properties(props) {
    var prop = jQuery('#advfrm-fields tbody tr.selected input[name="advfrm-props[]"]');
    if (props == null) { // get
        return prop.val().split('\u00A6');
    } else { // set
        prop.val(props.join('\u00A6'));
        return null;
    }
}


/**
 * Adjusts properties when type is changed.
 *
 * @return {undefined}
 */
function advfrm_changeType() {
    var props = advfrm_properties();
    var wasSelect = advfrm_isSelect(this.oldvalue);
    var isSelect = advfrm_isSelect(this.value);
    if (wasSelect !== isSelect) {
        var val = isSelect ? '\u00A6' : '\u00A6\u00A6\u00A6';
        jQuery('#advfrm-fields tbody tr.selected input[name="advfrm-props[]"]').val(val);
    } else if (wasSelect && isSelect
            && advfrm_isRealSelect(this.oldvalue) != advfrm_isRealSelect(this.value)) {
        props[ADVFRM_PROP_SIZE] = '';
        advfrm_properties(props);
    } else if (this.oldvalue == 'textarea' || this.value == 'textarea') {
        props[ADVFRM_PROP_ROWS] = '';
        advfrm_properties(props);
    }
    this.oldvalue = this.value;
}


/**
 * Highlights the selected row.
 *
 * @param  {String} id   The <div>s id.
 * @param  {Object} obj  The focused form element.
 * @return {undefined}
 */
function advfrm_highlightRow(id, obj) {
    jQuery('#' + id + ' tbody tr').removeClass('selected');
    obj.parent().parent().addClass('selected');
}


/**
 * Sets the label according to the field name.
 *
 * @return {undefined}
 */
function advfrm_fillLabel() {
    var lbl = jQuery('#advfrm-fields tbody tr.selected input[name="advfrm-label[]"]');
    if (lbl.val() == '') {
        lbl.val(jQuery(this).val());
    }
}


/**
 * Adds new row.
 *
 * @param  {String} id  The <div>s id.
 * @return {undefined}
 */
function advfrm_add(id) {
    if (id == 'advfrm-fields') {
        var row = jQuery('#advfrm-fields tbody tr:first').clone();
        row.removeClass('selected');
        row.find('input[type="text"]').val('');
        row.find('select').val('text');
        row.find('input[name="advfrm-props[]"]').val('\u00A6\u00A6\u00A6');
        row.find('input[type="checkbox"]').attr('checked', false);
        row.find('td > *[name]').focus(function() {advfrm_highlightRow('advfrm-fields', jQuery(this))});
        row.find('td > a').click(function() {advfrm_highlightRow('advfrm-fields', jQuery(this))});
        row.find('td > a').click(advfrm_props);
        row.find('td > input[name="advfrm-field[]"]').change(advfrm_fillLabel);
        row.find('td > select[name="advfrm-type[]"]').change(advfrm_changeType);
        row.find('td > input[name="advfrm-required[]"]').val(0);
        jQuery('#advfrm-fields').append(row);
        row.find('td > input[name="advfrm-field[]"]').focus();
    } else {
        var row = jQuery('#advfrm-prop-fields tbody tr:first').clone();
        row.removeClass('selected');
        row.find('input[name="advfrm-select-props-default"]').get(0).checked = false;
        row.find('input[name="advfrm-select-props-opt"]').val('')
                .focus(function() {advfrm_highlightRow('advfrm-prop-fields', jQuery(this))});
        jQuery('#advfrm-prop-fields').append(row);
        row.find('input[name="advfrm-select-props-opt"]').focus();
    }
}


/**
 * Deletes the selected row.
 *
 * @param {String} id  The <div>s id.
 * @return {undefined}
 */
function advfrm_delete(id) {
    if (jQuery('#' + id + ' tbody tr').length > 1) {
        jQuery('#' + id + ' tbody tr.selected').remove();
    }
}


/**
 * Moves the selected row up.
 *
 * @param {String} id  The <div>s id.
 * @return {undefined}
 */
function advfrm_up(id) {
    var sel = jQuery('#' + id + ' tbody tr.selected');
    sel.insertBefore(sel.prev());
}


/**
 * Moves the selected row down.
 *
 * @param {String} id  The <div>s id.
 * @return {undefined}
 */
function advfrm_down(id) {
    var sel = jQuery('#' + id + ' tbody tr.selected');
    sel.insertAfter(sel.next());
}


/**
 * Unchecks all selection defaults.
 *
 * @param {String} id  The <div>s id.
 * @return {undefined}
 */
function advfrm_clear_defaults(id) {
    jQuery('#'+id+' input[name="advfrm-select-props-default"]').each(function() {
        jQuery(this).get(0).checked = false;
    })
}


/**
 * Adjusts and opens the property dialog for the selected row.
 *
 * @return {undefined}
 */
function advfrm_props() {
    var type = jQuery('#advfrm-fields tr.selected select[name="advfrm-type[]"]').val();
    var title = ADVFRM_TX['label_properties'].replace(/%s/, ADVFRM_TX['field_'+type]);
    var props = advfrm_properties();
    var isSelect = advfrm_isSelect(type);
    var isRealSelect = advfrm_isRealSelect(type);
    var isMulti = advfrm_isMulti(type);
    var lbls = advfrm_propLabels(type);
    var vis = advfrm_visibleProps(type);
    if (isSelect) {
        var dlg = jQuery('#advfrm-select-props');
        if (isRealSelect) {
            dlg.find('#advfrm-select-props-orient').hide();
            dlg.find('#advfrm-select-props-size').show();
            dlg.find('#advfrm-select-props-size input').val(props[0]);
        } else {
            dlg.find('#advfrm-select-props-size').hide();
            dlg.find('#advfrm-select-props-orient').show();
            dlg.find('#advfrm-select-props-orient input').eq(props[0]).get(0).checked = true;
        }
        props = props.slice(1);
        dlg.find('tr').slice(1).remove();
        var inputType = isMulti ? 'checkbox' : 'radio';
        var radio = dlg.find('input[name="advfrm-select-props-default"]');
        radio.after('<input type="' + inputType + '" name="advfrm-select-props-default" />');
        radio.remove();
        var clone = dlg.find('tr').clone();
        clone.removeClass('selected');
        jQuery.each(props, function(i, elt) {
            if (elt.charAt(0) == '\u25CF') {
                var val = elt.substr(1);
                var checked = true;
            } else {
                var val = elt;
                var checked = false;
            }
            if (i == 0) {
                dlg.find('input[name="advfrm-select-props-opt"]').val(val);
                if (checked) {
                    dlg.find('input[name="advfrm-select-props-default"]').get(0).checked = true;
                }
            } else {
                var newclone = clone.clone();
                newclone.find('input[name="advfrm-select-props-opt"]').val(val);
                if (checked) {
                    newclone.find('input[name="advfrm-select-props-default"]').get(0).checked = true;
                }
                dlg.find('tr:last').after(newclone);
            }
        });
        dlg.find('input[name="advfrm-select-props-opt"]').focus(function() {advfrm_highlightRow('advfrm-prop-fields', jQuery(this))});
    } else {
        var dlg = jQuery('#advfrm-text-props');
        var rows = dlg.find('table tr');
        rows.hide();
        for (var i = 0; i < vis.length; i++) {
            var row = rows.eq(vis[i]);
            row.find('td:eq(0)').text(ADVFRM_TX['label_'+lbls[i]]);
            if (vis[i] == ADVFRM_PROP_DEFAULT) {
                var repl = jQuery.inArray(type, ['textarea', 'output', 'file']) > -1
                        ? '<textarea cols="30"></textarea>'
                        : '<input type="text" size="30"/>';
                row.find('input, textarea').replaceWith(repl);
            }
            row.show();
        }
        rows.find('input, textarea').each(function(i) {
            jQuery(this).val(props[i]);
        })
    }
    dlg.dialog('option', 'title', title);
    dlg.dialog('open');
}


/**
 * Returns if property values are valid.
 *
 * @return {boolean}
 */
function advfrm_checkProperties() {
    var type = jQuery('#advfrm-fields tr.selected select[name="advfrm-type[]"]').val();
    if (advfrm_isSelect(type)) {
        if (advfrm_isRealSelect(type)) {
            var elt = jQuery('#advfrm-select-props-size input');
            if (elt.val().match(/^[0-9]*$/) == null) {
                alert(ADVFRM_TX['error_invalid_property']);
                elt.focus();
                return false;
            }
        }
        var ok = true;
        jQuery('#advfrm-select-props input[name="advfrm-select-props-opt"]').each(function() {
            if (jQuery(this).val().match(/\u00A6|\u25CF/)) {
                alert(ADVFRM_TX['error_invalid_property']);
                jQuery(this).focus();
                ok = false;
                return false;
            } else {
                return true;
            }
        });
        return ok;
    } else {
        var vis = advfrm_visibleProps(type);
        for (var i = 0; i <= 1; i++) {
            if (jQuery.inArray(i, vis) > -1) {
                var elt = jQuery('#advfrm-text-props input').eq(i);
                if (elt.val().match(/^[0-9]*$/) == null) {
                    alert(ADVFRM_TX['error_invalid_property']);
                    elt.focus();
                    return false;
                }
            }
        }
        if (jQuery.inArray(type, ['textarea', 'hidden', 'output']) < 0) {
            var maxlen = jQuery('#advfrm-text-props input').eq(ADVFRM_PROP_MAXLEN);
            var def = jQuery('#advfrm-text-props input').eq(ADVFRM_PROP_DEFAULT);
            if (maxlen.val() != '' && def.val().length > maxlen.val()) {
                alert(ADVFRM_TX['error_default_too_long']);
                def.focus();
                return false;
            }
        }
    }
    return true;
}


/**
 * Returns if form input is valid.
 *
 * @return {boolean}
 */
function advfrm_checkForm() {
    var req = ['name', 'title', 'to'];
    for (var i = 0; i < req.length; i++) {
        var fld = jQuery('#advfrm-' + req[i]);
        if (fld.val() == '') {
            alert(ADVFRM_TX['error_missing_fields']);
            fld.focus();
            return false;
        }
        if (req[i] == 'name') {
            if (fld.val().match(/^[a-z0-9_]+$/i) == null) {
                alert(ADVFRM_TX['error_invalid_field']);
                fld.focus();
                return false;
            }
        }
    }

    var flds = jQuery('#advfrm-fields tbody input[name="advfrm-field[]"]');
    for (var i = 0; i < flds.length; i++) {
        var fld = jQuery(flds[i]);
        if (fld.val() == '') {
            alert(ADVFRM_TX['error_missing_fields']);
            fld.focus();
            return false;
        }
        if (fld.val().match(/^[a-z0-9_]+$/i) == null) {
            alert(ADVFRM_TX['error_invalid_field']);
            fld.focus();
            return false;
        }
    }
    for (var i = 0; i < flds.length-1; i++) {
        for (var j = i+1; j < flds.length; j++) {
            var fld = jQuery(flds[j]);
            if (fld.val() == jQuery(flds[i]).val()) {
                alert(ADVFRM_TX['error_duplicate_fields']);
                fld.focus();
                return false;
            }
        }
    }

    var flds = jQuery('#advfrm-fields select[name="advfrm-type[]"]');
    var fromName = 0;
    var from = 0;
    for (var i = 0; i < flds.length; i++) {
        if (flds[i].value == 'from_name') {
            if (++fromName > 1) {
                alert(ADVFRM_TX['error_too_many_senders']);
                jQuery(flds[i]).focus();
                return false;
            }
        } else if (flds[i].value == 'from') {
            if (++from > 1) {
                alert(ADVFRM_TX['error_too_many_senders']);
                jQuery(flds[i]).focus();
                return false;
            }
        }
    }

    return true;
}


/**
 * Gets the name of the form to import
 * and relocates to the given URL with the name appended.
 *
 * @param {String} url
 * @return {undefined}
 */
function advfrm_import(url) {
    var name = window.prompt(ADVFRM_TX['message_import_form']);
    if (name.search(/^[a-z0-9_]+$/i) >= 0) {
        window.location.href = url+name;
    }
}


/**
 * Initialization.
 */
jQuery(function() {
    //jQuery('#advfrm-accordion').accordion(); ACCORDION!
    jQuery('#advfrm-name').change(function() {
        var title = jQuery('#advfrm-title');
        if (title.val() == '') {
            title.val(jQuery(this).val());
        }
    });
    jQuery('#advfrm-name').focus();
    jQuery('#advfrm-fields tbody td > *[name]').focus(function() {advfrm_highlightRow('advfrm-fields', jQuery(this))});
    jQuery('#advfrm-fields tbody td > a').click(function() {advfrm_highlightRow('advfrm-fields', jQuery(this))});
    jQuery('#advfrm-fields tbody td > a').click(advfrm_props);
    jQuery('#advfrm-fields tbody input[name="advfrm-field[]"]').change(advfrm_fillLabel);
    jQuery('#advfrm-fields tbody select[name="advfrm-type[]"]').change(advfrm_changeType);

    jQuery('#advfrm-editor input.submit').show();

    jQuery('#advfrm-text-props').dialog({
        autoOpen: false,
        modal: true,
        width: 450,
        buttons: [{
            text: ADVFRM_TX['button_ok'],
            click: function() {
                if (!advfrm_checkProperties()) {
                    return;
                }
                advfrm_properties(jQuery(this).find('input, textarea').map(function() {
                    return jQuery(this).val();
                }).get());
                jQuery(this).dialog('close');
            }
        }, {
            text: ADVFRM_TX['button_cancel'],
            click: function() {jQuery(this).dialog('close')}
        }]
    });

    jQuery('#advfrm-select-props').dialog({
        autoOpen: false,
        modal: true,
        buttons: [
            {
                text: ADVFRM_TX['button_ok'],
                click: function() {
                    if (!advfrm_checkProperties()) {
                        return;
                    }
                    var props = jQuery(this).find('input[name="advfrm-select-props-opt"]').map(function() {
                        var def = jQuery(this).parents('tr').find('input[name="advfrm-select-props-default"]').get(0).checked ? '\u25CF' : '';
                        return def + jQuery(this).val();
                    }).get();
                    props.unshift(jQuery('#advfrm-select-props-size').css('display') == 'none'
                            ? (jQuery('#advfrm-select-props-orient input').get(1).checked ? 1 : 0)
                            : jQuery('#advfrm-select-props-size input').val());
                    advfrm_properties(props);
                    jQuery(this).dialog('close');
                }
            },
            {
                text: ADVFRM_TX['button_cancel'],
                click: function() {jQuery(this).dialog('close')}
            }
        ]
    });
});
