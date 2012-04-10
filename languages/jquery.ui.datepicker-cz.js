jQuery(function($){
	$.datepicker.regional['cz'] = {
		closeText: 'schließen',
		prevText: '&#x3c;Zpět',
		nextText: 'Další&#x3e;',
		currentText: 'dnes',
		monthNames: ['Leden','Únor','Březen','Duben','Květen','Červen','Červenec','Srpen','Září','Říjen','Listopad','Prosinec'],
		monthNamesShort: ['Led','Ún','Bře','Du','Kvě','Čer','Črnec','Sr','Zá','Říj','Li','Pro'],
		dayNames: ['Neděle','Pondělí','Úterý','Středa','Čtvrtek','Pátek','Sobota'],
		dayNamesShort: ['Ne','Po','Út','St','Čt','Pá','So'],
		dayNamesMin: ['Ne','Po','Út','St','Čt','Pá','So'],
		weekHeader: 'Wo',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['cz']);
});
