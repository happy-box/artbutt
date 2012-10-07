var currentColor = 'w';
var delMode = false;

var numRows = 15;
var numCols = 26;

var listFrom = 0;
var listNum = 10;

var fillings = new Array ( );
var filled = new Array ( );

var colors = [
	'W',
	'A',
	'B',
	'C',
	'D',
	'E',
	'F',
	'G',
	'H',
	'I',
	'J',
	'K',
	'L',
	'M',
	'N',
	'O'
];

var irccolors = [
	'01',
	'00',
	'02',
	'03',
	'04',
	'05',
	'06',
	'07',
	'08',
	'09',
	'10',
	'11',
	'12',
	'13',
	'14',
	'15'
];



function init ( )
{

	for ( i=0; i<colors.length; i++ )
	{

		$("#palette .colors").append ( '<li class="' + colors[i].toLowerCase() + '" title="color code: ' + colors[i].toLowerCase() + '">&nbsp;</li>')

	}

	$("#palette .colors").append ( '<li class="empty" title="Delete stuff"></li>');
	$("#palette").hide ( );

	$("#grid thead tr").append ( '<th></th>' );

	for ( i=0; i<numRows; i++ )
	{

		var insertRows = '<tr><td class="r">' + (i+1) + '</td>';

		for ( j=0; j<numCols; j++)
		{
			if ( i==0 )
				$("#grid thead tr").append ( '<th>' + (j+1) + '</th>' );

			insertRows += '<td></td>';
		}

		insertRows += '</tr>';
		$("#grid tbody").append ( insertRows );

	}


	//fillPalette ( 'empty' );
	var loadDeer = 'deer';
	
	if ( location.hash.substring(1) != "" && location.hash.substring(1) != false )
		loadDeer = location.hash.substring(1);
			
	deerLoad ( loadDeer, true );
	deerList ( );

}

function pickColor ( pick )
{

	if ( pick == 'empty' )
	{
		delMode = true;
	}
	else
	{
		delMode = false;
	}
	$("#pick").removeClass ( currentColor ).addClass ( pick );
	currentColor = pick;
	$("#palette").hide ( );

}

function fillCell ( x, y, className )
{

	filled[y][x] = className;

}

function fillPalette ( fillColor )
{

	for ( x=0; x<numCols; x++ ) // x
		fillings[x] = fillColor;

	for ( r=0; r<numRows; r++) // y
		filled[r] = fillings.slice(0);

	$("#grid tbody td").each ( function ()  {

		if ( $(this).hasClass("r") != true )
			$(this).attr("class", fillColor );

	});

}

function save ( deername, deercreator )
{

	var cropped = autoCrop ( );

	$.post ( 'deer.php', {
		name: deername,
		creator: deercreator,
		'data[]': cropped
	}, function ( result )
	{

		prompt ( "The deer is complete!", result.name );
		var output = str_replace ( ' ', '&nbsp;', result.raw );
		$("#output").html ( output ).select();
		$("#ircoutput").html ( result.ircraw );


	}, 'json');

}



function reverse_engideer ( irc, skipConfirm, kinskode )
{

	if ( !skipConfirm )
		if ( !confirm ( "Are you sure? This can't be undone." ) )
			return;


	fillPalette ( 'empty' );

	if ( irc==false )
	{
		var input = $("#output").val();
	}
	else
	{

		var input = $("#ircoutput").val();

		input = input.replace(/\u0003[0-9]+\,/gi, '' );
		input = input.replace(/\u000F/gi, '');
		//input = input.replace(/[a-zA-Z0-9]/gi, '@');
		input = str_replace ( irccolors, colors, input );
		input = str_replace ( '@', '', input );

	}

	input = input.split ( '\n' );

	for ( i=0; i<input.length; i++ )
	{
		var reg = /[a-zA-Z\_\ ]+/ig;
		var line = input[i].match ( reg );

		if ( line != null )
		{
			line = line.toString().split("");
			for ( l=0; l<line.length; l++ )
			{

				className = line[l].toLowerCase ( );

				if ( className == '_' )
					className = 'empty';
				else if ( className == ' ' )
					className = 'w';

				fillCell ( l, i, className.toLowerCase ( ) );
				$("#grid tbody tr").eq(i).find("td").not(".r").eq(l).attr("class", className.toLowerCase ( ) );

			}
		}

	}

}

function autoCrop ( )
{

	// some awesome algorithm for deciding when image starts and ends!

	var cropped = new Array ( );

	var xLowDefined = false;
	var yLowDefined = false;
	var xHighDefined = false;
	var yHighDefined = false;

	var yLow = 0;
	var yHigh = (numRows-1);
	var xLow = 0;
	var xHigh = (numCols-1);

	for ( x=0; x<numCols; x++ )
	{

		for ( y=0; y<numRows; y++ )
		{
			if ( filled[y][x] != 'empty' )
			{

				if ( xLowDefined == false || x < xLow )
				{
					xLow = x;
					xLowDefined = true;
				}

				if ( yLowDefined == false || y < yLow )
				{
					yLow = y;
					yLowDefined = true;
				}

				if ( xHighDefined == false || x > xHigh )
				{
					xHigh = x;
					xHighDefined = true;
				}

				if ( yHighDefined == false || y > yHigh )
				{
					yHigh = y;
					yHighDefined = true;
				}
			}

		}

	}

	cropped = filled.slice ( yLow, yHigh+1 );

	for ( i=0; i<cropped.length; i++ )
		cropped[i] = cropped[i].slice ( xLow, xHigh+1 );

	return cropped;


}


function deerList ( )
{

	$("#deerlist").children().each( function ( ) { $(this).remove(); });
	$("#deerload").show ( );
	$.getJSON( "deerlist.php?start=" + listFrom + "&callback=?",
	function ( data )
	{
		$("#deerload").hide ( );
		if ( data.status == 'list' )
		{

			$.each ( data.deer, function ( i, item )
			{
				$("#deerlist").append ( '<li class="predeer"><b title="' + item.deer + '">' + item.deer + '</b> by ' + item.creator + '</li>' );
			});

			$(".predeer").click( function ( ) {

				var deerTitle = $(this).find("b").attr("title");
				deerLoad ( deerTitle );

			});

		}
		else
			$("#deerlist").append ( '<li>' + data.error + '</li>' );

	});

}

function deerLoad ( deer, skipConfirm )
{
	if ( skipConfirm ) skipConfirm = true;

	$.getJSON( "deerlist.php?deer=" + deer + "&callback=?",
	function ( data )
	{

		if ( data.status == 'found' )
		{
			location.href = '#' + data.deer;
			$("#output").val( data.kinskode );
			$("#ircoutput").val( data.irccode );
			register_deer ( data.deer );
			reverse_engideer ( false, skipConfirm );
		}
		else
			alert ( "Something went wrong: " + data.error );

	});

}

function register_deer ( deer )
{

	var export_types = [ "jpg", "png", "svg" ];
	var html = '';
	
	for ( var type in export_types )
	{
		html += '<li><a href="img/' + deer + '.' + export_types[type] + '" title="Export to ' + export_types[type] + '">Export to ' + export_types[type] + '</a></li>';
	}
	
	$('#export-type-list').html( html );

}

function str_replace(search, replace, subject) {
    var f = search, r = replace, s = subject;
    var ra = r instanceof Array, sa = s instanceof Array, f = [].concat(f), r = [].concat(r), i = (s = [].concat(s)).length;

    while (j = 0, i--) {
        if (s[i]) {
            while (s[i] = s[i].split(f[j]).join(ra ? r[j] || "" : r[0]), ++j in f){};
        }
    };

    return sa ? s : s[0];
}

function foundDeer ( event, data, formatted )
{

	if ( !data )
		alert ( "No such deer...?" );
	else
		deerLoad ( formatted );

}

$(document).ready( function ( )
{

	init ( );

	$("#grid tbody tr td").not(".r").hover ( function ( )
	{
		$(this).addClass ( "tdhover" );
	},
	function ( )
	{
		$(this).removeClass ( "tdhover" );

	}).mouseup( function( ) {

		$("#grid tbody tr td").not(".r").unbind("mousemove");

	}).mousedown ( function ( ) {

		var lastX = 0;
		var lastY = 0;
		var thisX = 0;
		var thisY = 0;
		var clickX = ( $(this).get(0).cellIndex - 1 );
		var clickY = ( $(this).parent().get(0).rowIndex - 1 );

		$(this).attr("class", currentColor );
		fillCell ( clickX,clickY, currentColor );

		$("#grid tbody tr td").not(".r").mousemove ( function ( ) {

			$("#grid").focus();

			thisX = ($(this).get(0).cellIndex - 1);
			thisY = ($(this).parent().get(0).rowIndex - 1);

			if ( lastX != thisX || lastY != thisY  )
			{
				lastX = thisX;
				lastY = thisY;

				$(this).attr("class", currentColor );
				fillCell ( lastX, lastY, currentColor );
			}

		});

	}).css ("cursor", "crosshair");

	$("#pick").click ( function ( ) {

		$("#palette").toggle ( );

	}).addClass ( currentColor );

	$("#palette .colors li").click ( function ( ) {

		pickColor ( $(this).attr("class") );

	});

	$("#tools li").click ( function ( )
	{

		switch ( $(this).attr("class") )
		{

			case 'fill_canvas':

				if ( confirm ( "Are you sure? This can't be undone." ) )
					fillPalette ( currentColor );

			break;

			case 'reset_canvas':

				if ( confirm ( "Are you sure? This can't be undone." ) )
					fillPalette ( 'empty' );
			break;
			
			case 'load_img':
			
				var img = prompt ( 'Image URL', 'http://' );
				if ( img!=null&&img!='')
				{ 
					$.getJSON('deerimg.php?_img=' + img + '&callback=?',function(data){
						if ( data.status=='success' )
						{
							$('#output').val(data.kinskode);
							reverse_engideer ( false );
							
						}
					});
				}
			
			break;

			case 'test_crop':
				autoCrop ( );
			break;
			default:

		}

	});

	$("#rebuild").click ( function ( )
	{
		reverse_engideer ( false );
	});

	$("#rebuild_irc").click ( function ( )
	{
		reverse_engideer ( true );
	});

	$("#save").click ( function ( ) {

		$("#savebox").dialog("open");

	});

	$("#refreshlist").click ( function ( ) {

		deerList ( );

	});

	$("#prevlist").click ( function ( ) {

		listFrom = listFrom-listNum;
		if ( listFrom <= 0 )
			listFrom = 0;

		deerList ( );

	});

	$("#nextlist").click ( function ( ) {

		listFrom = listFrom+listNum;
		deerList ( );

	});

	$("#dosave").click ( function ( ) {

		deername =  ( $("#deername").val() != '' ) ? $("#deername").val() : 'deer';
		deercreator = ( $("#deercreator").val() != '' ) ? $("#deercreator").val() : 'Anonydeer';

		save ( deername, deercreator );
		$("#savebox").dialog("close");
		listFrom = 0;
		deerList ( );

	});

	$("#savebox").dialog (
	{
		modal: true,
		resize: false,
		autoOpen: false,
		dialogClass: 'flora',
		overlay: {
			opacity: 0.65,
			background: 'black'
		}
	});

	$("#deerfinder")
		.forb ( )
		.autocomplete ( 'deerlist.php', {
			mustMatch: true
		})
		.result ( foundDeer )
	;

});
