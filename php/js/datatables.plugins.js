/**
 * contains the following pagination types
 * select list (listbox)
 * scrolling
 * input
 * four_button
 *
 */


$.fn.dataTableExt.oPagination.listbox = {
	    /*
	     * Function: oPagination.listbox.fnInit
	     * Purpose:  Initalise dom elements required for pagination with listbox input
	     * Returns:  -
	     * Inputs:   object:oSettings - dataTables settings object
	     *             node:nPaging - the DIV which contains this pagination control
	     *             function:fnCallbackDraw - draw function which must be called on update
	     */
	    "fnInit": function (oSettings, nPaging, fnCallbackDraw) {
	        var nInput = document.createElement('select');
	        var nPage = document.createElement('span');
	        var nOf = document.createElement('span');
	        nOf.className = "paginate_of";
	        nPage.className = "paginate_page";
	        if (oSettings.sTableId !== '') {
	            nPaging.setAttribute('id', oSettings.sTableId + '_paginate');
	        }
	        nInput.style.display = "inline";
	        nPage.innerHTML = "Page ";
	        nPaging.appendChild(nPage);
	        nPaging.appendChild(nInput);
	        nPaging.appendChild(nOf);
	        $(nInput).change(function (e) { // Set DataTables page property and redraw the grid on listbox change event.
	            window.scroll(0,0); //scroll to top of page
	            if (this.value === "" || this.value.match(/[^0-9]/)) { /* Nothing entered or non-numeric character */
	                return;
	            }
	            var iNewStart = oSettings._iDisplayLength * (this.value - 1);
	            if (iNewStart > oSettings.fnRecordsDisplay()) { /* Display overrun */
	                oSettings._iDisplayStart = (Math.ceil((oSettings.fnRecordsDisplay() - 1) / oSettings._iDisplayLength) - 1) * oSettings._iDisplayLength;
	                fnCallbackDraw(oSettings);
	                return;
	            }
	            oSettings._iDisplayStart = iNewStart;
	            fnCallbackDraw(oSettings);
	        }); /* Take the brutal approach to cancelling text selection */
	        $('span', nPaging).bind('mousedown', function () {
	            return false;
	        });
	        $('span', nPaging).bind('selectstart', function () {
	            return false;
	        });
	    },

	    /*
	     * Function: oPagination.listbox.fnUpdate
	     * Purpose:  Update the listbox element
	     * Returns:  -
	     * Inputs:   object:oSettings - dataTables settings object
	     *             function:fnCallbackDraw - draw function which must be called on update
	     */
	    "fnUpdate": function (oSettings, fnCallbackDraw) {
	        if (!oSettings.aanFeatures.p) {
	            return;
	        }
	        var iPages = Math.ceil((oSettings.fnRecordsDisplay()) / oSettings._iDisplayLength);
	        var iCurrentPage = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1; /* Loop over each instance of the pager */
	        var an = oSettings.aanFeatures.p;
	        for (var i = 0, iLen = an.length; i < iLen; i++) {
	            var spans = an[i].getElementsByTagName('span');
	            var inputs = an[i].getElementsByTagName('select');
	            var elSel = inputs[0];
	            if(elSel.options.length != iPages) {
	                elSel.options.length = 0; //clear the listbox contents
	                for (var j = 0; j < iPages; j++) { //add the pages
	                    var oOption = document.createElement('option');
	                    oOption.text = j + 1;
	                    oOption.value = j + 1;
	                    try {
	                        elSel.add(oOption, null); // standards compliant; doesn't work in IE
	                    } catch (ex) {
	                        elSel.add(oOption); // IE only
	                    }
	                }
	                spans[1].innerHTML = " nbsp;of nbsp;" + iPages;
	            }
	          elSel.value = iCurrentPage;
	        }
	    }
	};


$.fn.dataTableExt.oPagination.input = {
	    "fnInit": function ( oSettings, nPaging, fnCallbackDraw )
	    {
	        var nFirst = document.createElement( 'span' );
	        var nPrevious = document.createElement( 'span' );
	        var nNext = document.createElement( 'span' );
	        var nLast = document.createElement( 'span' );
	        var nInput = document.createElement( 'input' );
	        var nPage = document.createElement( 'span' );
	        var nOf = document.createElement( 'span' );

	        nFirst.innerHTML = oSettings.oLanguage.oPaginate.sFirst;
	        nPrevious.innerHTML = oSettings.oLanguage.oPaginate.sPrevious;
	        nNext.innerHTML = oSettings.oLanguage.oPaginate.sNext;
	        nLast.innerHTML = oSettings.oLanguage.oPaginate.sLast;

	        nFirst.className = "paginate_button first";
	        nPrevious.className = "paginate_button previous";
	        nNext.className="paginate_button next";
	        nLast.className = "paginate_button last";
	        nOf.className = "paginate_of";
	        nPage.className = "paginate_page";

	        if ( oSettings.sTableId !== '' )
	        {
	            nPaging.setAttribute( 'id', oSettings.sTableId+'_paginate' );
	            nPrevious.setAttribute( 'id', oSettings.sTableId+'_previous' );
	            nPrevious.setAttribute( 'id', oSettings.sTableId+'_previous' );
	            nNext.setAttribute( 'id', oSettings.sTableId+'_next' );
	            nLast.setAttribute( 'id', oSettings.sTableId+'_last' );
	        }

	        nInput.type = "text";
	        nInput.style.width = "15px";
	        nInput.style.display = "inline";
	        nPage.innerHTML = "Page ";

	        nPaging.appendChild( nFirst );
	        nPaging.appendChild( nPrevious );
	        nPaging.appendChild( nPage );
	        nPaging.appendChild( nInput );
	        nPaging.appendChild( nOf );
	        nPaging.appendChild( nNext );
	        nPaging.appendChild( nLast );

	        $(nFirst).click( function () {
	            oSettings.oApi._fnPageChange( oSettings, "first" );
	            fnCallbackDraw( oSettings );
	        } );

	        $(nPrevious).click( function() {
	            oSettings.oApi._fnPageChange( oSettings, "previous" );
	            fnCallbackDraw( oSettings );
	        } );

	        $(nNext).click( function() {
	            oSettings.oApi._fnPageChange( oSettings, "next" );
	            fnCallbackDraw( oSettings );
	        } );

	        $(nLast).click( function() {
	            oSettings.oApi._fnPageChange( oSettings, "last" );
	            fnCallbackDraw( oSettings );
	        } );

	        $(nInput).keyup( function (e) {

	            if ( e.which == 38 || e.which == 39 )
	            {
	                this.value++;
	            }
	            else if ( (e.which == 37 || e.which == 40) && this.value > 1 )
	            {
	                this.value--;
	            }

	            if ( this.value == "" || this.value.match(/[^0-9]/) )
	            {
	                /* Nothing entered or non-numeric character */
	                return;
	            }

	            var iNewStart = oSettings._iDisplayLength * (this.value - 1);
	            if ( iNewStart > oSettings.fnRecordsDisplay() )
	            {
	                /* Display overrun */
	                oSettings._iDisplayStart = (Math.ceil((oSettings.fnRecordsDisplay()-1) /
	                    oSettings._iDisplayLength)-1) * oSettings._iDisplayLength;
	                fnCallbackDraw( oSettings );
	                return;
	            }

	            oSettings._iDisplayStart = iNewStart;
	            fnCallbackDraw( oSettings );
	        } );

	        /* Take the brutal approach to cancelling text selection */
	        $('span', nPaging).bind( 'mousedown', function () { return false; } );
	        $('span', nPaging).bind( 'selectstart', function () { return false; } );
	    },


	    "fnUpdate": function ( oSettings, fnCallbackDraw )
	    {
	        if ( !oSettings.aanFeatures.p )
	        {
	            return;
	        }
	        var iPages = Math.ceil((oSettings.fnRecordsDisplay()) / oSettings._iDisplayLength);
	        var iCurrentPage = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1;

	        /* Loop over each instance of the pager */
	        var an = oSettings.aanFeatures.p;
	        for ( var i=0, iLen=an.length ; i<iLen ; i++ )
	        {
	            var spans = an[i].getElementsByTagName('span');
	            var inputs = an[i].getElementsByTagName('input');
	            spans[3].innerHTML = " of "+iPages
	            inputs[0].value = iCurrentPage;
	        }
	    }
	};

$.fn.dataTableExt.oPagination.four_button = {
	    "fnInit": function ( oSettings, nPaging, fnCallbackDraw )
	    {
	        nFirst = document.createElement( 'span' );
	        nPrevious = document.createElement( 'span' );
	        nNext = document.createElement( 'span' );
	        nLast = document.createElement( 'span' );

	        nFirst.appendChild( document.createTextNode( oSettings.oLanguage.oPaginate.sFirst ) );
	        nPrevious.appendChild( document.createTextNode( oSettings.oLanguage.oPaginate.sPrevious ) );
	        nNext.appendChild( document.createTextNode( oSettings.oLanguage.oPaginate.sNext ) );
	        nLast.appendChild( document.createTextNode( oSettings.oLanguage.oPaginate.sLast ) );

	        nFirst.className = "paginate_button first";
	        nPrevious.className = "paginate_button previous";
	        nNext.className="paginate_button next";
	        nLast.className = "paginate_button last";

	        nPaging.appendChild( nFirst );
	        nPaging.appendChild( nPrevious );
	        nPaging.appendChild( nNext );
	        nPaging.appendChild( nLast );

	        $(nFirst).click( function () {
	            oSettings.oApi._fnPageChange( oSettings, "first" );
	            fnCallbackDraw( oSettings );
	        } );

	        $(nPrevious).click( function() {
	            oSettings.oApi._fnPageChange( oSettings, "previous" );
	            fnCallbackDraw( oSettings );
	        } );

	        $(nNext).click( function() {
	            oSettings.oApi._fnPageChange( oSettings, "next" );
	            fnCallbackDraw( oSettings );
	        } );

	        $(nLast).click( function() {
	            oSettings.oApi._fnPageChange( oSettings, "last" );
	            fnCallbackDraw( oSettings );
	        } );

	        /* Disallow text selection */
	        $(nFirst).bind( 'selectstart', function () { return false; } );
	        $(nPrevious).bind( 'selectstart', function () { return false; } );
	        $(nNext).bind( 'selectstart', function () { return false; } );
	        $(nLast).bind( 'selectstart', function () { return false; } );
	    },


	    "fnUpdate": function ( oSettings, fnCallbackDraw )
	    {
	        if ( !oSettings.aanFeatures.p )
	        {
	            return;
	        }

	        /* Loop over each instance of the pager */
	        var an = oSettings.aanFeatures.p;
	        for ( var i=0, iLen=an.length ; i<iLen ; i++ )
	        {
	            var buttons = an[i].getElementsByTagName('span');
	            if ( oSettings._iDisplayStart === 0 )
	            {
	                buttons[0].className = "paginate_disabled_previous";
	                buttons[1].className = "paginate_disabled_previous";
	            }
	            else
	            {
	                buttons[0].className = "paginate_enabled_previous";
	                buttons[1].className = "paginate_enabled_previous";
	            }

	            if ( oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay() )
	            {
	                buttons[2].className = "paginate_disabled_next";
	                buttons[3].className = "paginate_disabled_next";
	            }
	            else
	            {
	                buttons[2].className = "paginate_enabled_next";
	                buttons[3].className = "paginate_enabled_next";
	            }
	        }
	    }
	};

/* Time between each scrolling frame */
$.fn.dataTableExt.oPagination.iTweenTime = 100;

$.fn.dataTableExt.oPagination.scrolling = {
    "fnInit": function ( oSettings, nPaging, fnCallbackDraw )
    {
        var oLang = oSettings.oLanguage.oPaginate;
        var oClasses = oSettings.oClasses;
        var fnClickHandler = function ( e ) {
            if ( oSettings.oApi._fnPageChange( oSettings, e.data.action ) )
            {
                fnCallbackDraw( oSettings );
            }
        };

        var sAppend = (!oSettings.bJUI) ?
            '<a class="'+oSettings.oClasses.sPagePrevDisabled+'" tabindex="'+oSettings.iTabIndex+'" role="button">'+oLang.sPrevious+'</a>'+
            '<a class="'+oSettings.oClasses.sPageNextDisabled+'" tabindex="'+oSettings.iTabIndex+'" role="button">'+oLang.sNext+'</a>'
            :
            '<a class="'+oSettings.oClasses.sPagePrevDisabled+'" tabindex="'+oSettings.iTabIndex+'" role="button"><span class="'+oSettings.oClasses.sPageJUIPrev+'"></span></a>'+
            '<a class="'+oSettings.oClasses.sPageNextDisabled+'" tabindex="'+oSettings.iTabIndex+'" role="button"><span class="'+oSettings.oClasses.sPageJUINext+'"></span></a>';
        $(nPaging).append( sAppend );

        var els = $('a', nPaging);
        var nPrevious = els[0],
            nNext = els[1];

        oSettings.oApi._fnBindAction( nPrevious, {action: "previous"}, function() {
            /* Disallow paging event during a current paging event */
            if ( typeof oSettings.iPagingLoopStart != 'undefined' && oSettings.iPagingLoopStart != -1 )
            {
                return;
            }

            oSettings.iPagingLoopStart = oSettings._iDisplayStart;
            oSettings.iPagingEnd = oSettings._iDisplayStart - oSettings._iDisplayLength;

            /* Correct for underrun */
            if ( oSettings.iPagingEnd < 0 )
            {
              oSettings.iPagingEnd = 0;
            }

            var iTween = $.fn.dataTableExt.oPagination.iTweenTime;
            var innerLoop = function () {
                if ( oSettings.iPagingLoopStart > oSettings.iPagingEnd ) {
                    oSettings.iPagingLoopStart--;
                    oSettings._iDisplayStart = oSettings.iPagingLoopStart;
                    fnCallbackDraw( oSettings );
                    setTimeout( function() { innerLoop(); }, iTween );
                } else {
                    oSettings.iPagingLoopStart = -1;
                }
            };
            innerLoop();
        } );

        oSettings.oApi._fnBindAction( nNext, {action: "next"}, function() {
            /* Disallow paging event during a current paging event */
            if ( typeof oSettings.iPagingLoopStart != 'undefined' && oSettings.iPagingLoopStart != -1 )
            {
                return;
            }

            oSettings.iPagingLoopStart = oSettings._iDisplayStart;

            /* Make sure we are not over running the display array */
            if ( oSettings._iDisplayStart + oSettings._iDisplayLength < oSettings.fnRecordsDisplay() )
            {
                oSettings.iPagingEnd = oSettings._iDisplayStart + oSettings._iDisplayLength;
            }

            var iTween = $.fn.dataTableExt.oPagination.iTweenTime;
            var innerLoop = function () {
                if ( oSettings.iPagingLoopStart < oSettings.iPagingEnd ) {
                    oSettings.iPagingLoopStart++;
                    oSettings._iDisplayStart = oSettings.iPagingLoopStart;
                    fnCallbackDraw( oSettings );
                    setTimeout( function() { innerLoop(); }, iTween );
                } else {
                    oSettings.iPagingLoopStart = -1;
                }
            };
            innerLoop();
        } );
    },

    "fnUpdate": function ( oSettings, fnCallbackDraw )
    {
        if ( !oSettings.aanFeatures.p )
        {
            return;
        }

        /* Loop over each instance of the pager */
        var an = oSettings.aanFeatures.p;
        for ( var i=0, iLen=an.length ; i<iLen ; i++ )
        {
            if ( an[i].childNodes.length !== 0 )
            {
                an[i].childNodes[0].className =
                    ( oSettings._iDisplayStart === 0 ) ?
                    oSettings.oClasses.sPagePrevDisabled : oSettings.oClasses.sPagePrevEnabled;

                an[i].childNodes[1].className =
                    ( oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay() ) ?
                    oSettings.oClasses.sPageNextDisabled : oSettings.oClasses.sPageNextEnabled;
            }
        }
    }
};

/*
 * File:        ColumnFilterWidgets.js
 * Version:     1.0.2
 * Description: Controls for filtering based on unique column values in DataTables
 * Author:      Dylan Kuhn (www.cyberhobo.net)
 * Language:    Javascript
 * License:     GPL v2 or BSD 3 point style
 * Contact:     cyberhobo@cyberhobo.net
 *
 * Copyright 2011 Dylan Kuhn (except fnGetColumnData by Benedikt Forchhammer), all rights reserved.
 *
 * This source file is free software, under either the GPL v2 license or a
 * BSD style license, available at:
 *   http://datatables.net/license_gpl2
 *   http://datatables.net/license_bsd
 */

(function($) {
	/*
	 * Function: fnGetColumnData
	 * Purpose:  Return an array of table values from a particular column.
	 * Returns:  array string: 1d data array
	 * Inputs:   object:oSettings - dataTable settings object. This is always the last argument past to the function
	 *           int:iColumn - the id of the column to extract the data from
	 *           bool:bUnique - optional - if set to false duplicated values are not filtered out
	 *           bool:bFiltered - optional - if set to false all the table data is used (not only the filtered)
	 *           bool:bIgnoreEmpty - optional - if set to false empty values are not filtered from the result array
	 * Author:   Benedikt Forchhammer <b.forchhammer /AT\ mind2.de>
	 */

	$.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
		// check that we have a column id
		if ( typeof iColumn == "undefined" ) return new Array();

		// by default we only wany unique data
		if ( typeof bUnique == "undefined" ) bUnique = true;

		// by default we do want to only look at filtered data
		if ( typeof bFiltered == "undefined" ) bFiltered = true;

		// by default we do not wany to include empty values
		if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;

		// list of rows which we're going to loop through
		var aiRows;

		// use only filtered rows
		if (bFiltered == true) aiRows = oSettings.aiDisplay;
		// use all rows
		else aiRows = oSettings.aiDisplayMaster; // all row numbers

		// set up data array
		var asResultData = new Array();

		for (var i=0,c=aiRows.length; i<c; i++) {
			iRow = aiRows[i];
			var aData = this.fnGetData(iRow);
			var sValue = aData[iColumn];

			// ignore empty values?
			if (bIgnoreEmpty == true && sValue.length == 0) continue;

			// ignore unique values?
			else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;

			// else push the value onto the result data array
			else asResultData.push(sValue);
		}

		return asResultData;
	};

	/**
	* Add backslashes to regular expression symbols in a string.
	*
	* Allows a regular expression to be constructed to search for
	* variable text.
	*
	* @param string sText The text to escape.
	* @return string The escaped string.
	*/
	var fnRegExpEscape = function( sText ) {
		return sText.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
	};

	/**
	* Menu-based filter widgets based on distinct column values for a table.
	*
	* @class ColumnFilterWidgets
	* @constructor
	* @param {object} oDataTableSettings Settings for the target table.
	*/
	var ColumnFilterWidgets = function( oDataTableSettings ) {
		var me = this;
		var sExcludeList = '';
		me.$WidgetContainer = $( '<div class="column-filter-widgets"></div>' );
		me.$MenuContainer = me.$WidgetContainer;
		me.$TermContainer = null;
		me.aoWidgets = [];
		me.sSeparator = '';
		if ( 'oColumnFilterWidgets' in oDataTableSettings.oInit ) {
			if ( 'aiExclude' in oDataTableSettings.oInit.oColumnFilterWidgets ) {
				sExcludeList = '|' + oDataTableSettings.oInit.oColumnFilterWidgets.aiExclude.join( '|' ) + '|';
			}
			if ( 'bGroupTerms' in oDataTableSettings.oInit.oColumnFilterWidgets && oDataTableSettings.oInit.oColumnFilterWidgets.bGroupTerms ) {
				me.$MenuContainer = $( '<div class="column-filter-widget-menus"></div>' );
				me.$TermContainer = $( '<div class="column-filter-widget-selected-terms"></div>' ).hide();
			}
		}

		// Add a widget for each visible and filtered column
		$.each( oDataTableSettings.aoColumns, function ( i, oColumn ) {
			var $columnTh = $( oColumn.nTh );
			var $WidgetElem = $( '<div class="column-filter-widget"></div>' );
			if ( oColumn.bVisible && sExcludeList.indexOf( '|' + i + '|' ) < 0 ) {
				me.aoWidgets.push( new ColumnFilterWidget( $WidgetElem, oDataTableSettings, i, me ) );
			}
			me.$MenuContainer.append( $WidgetElem );
		} );
		if ( me.$TermContainer ) {
			me.$WidgetContainer.append( me.$MenuContainer );
			me.$WidgetContainer.append( me.$TermContainer );
		}
		oDataTableSettings.aoDrawCallback.push( {
			name: 'ColumnFilterWidgets',
			fn: function() {
				$.each( me.aoWidgets, function( i, oWidget ) {
					oWidget.fnDraw();
				} );
			}
		} );

		return me;
	};

	/**
	* Get the container node of the column filter widgets.
	*
	* @method
	* @return {Node} The container node.
	*/
	ColumnFilterWidgets.prototype.getContainer = function() {
		return this.$WidgetContainer.get( 0 );
	}

	/**
	* A filter widget based on data in a table column.
	*
	* @class ColumnFilterWidget
	* @constructor
	* @param {object} $Container The jQuery object that should contain the widget.
	* @param {object} oSettings The target table's settings.
	* @param {number} i The numeric index of the target table column.
	* @param {object} widgets The ColumnFilterWidgets instance the widget is a member of.
	*/
	var ColumnFilterWidget = function( $Container, oDataTableSettings, i, widgets ) {
		var widget = this;
		widget.iColumn = i;
		widget.oColumn = oDataTableSettings.aoColumns[i];
		widget.$Container = $Container;
		widget.oDataTable = oDataTableSettings.oInstance;
		widget.asFilters = [];
		widget.sSeparator = '';
		widget.iMaxSelections = -1;
		if ( 'oColumnFilterWidgets' in oDataTableSettings.oInit ) {
			if ( 'sSeparator' in oDataTableSettings.oInit.oColumnFilterWidgets ) {
				widget.sSeparator = oDataTableSettings.oInit.oColumnFilterWidgets.sSeparator;
			}
			if ( 'iMaxSelections' in oDataTableSettings.oInit.oColumnFilterWidgets ) {
				widget.iMaxSelections = oDataTableSettings.oInit.oColumnFilterWidgets.iMaxSelections;
			}
		}
		widget.$Select = $( '<select></select>' ).change( function() {
			var sSelected = widget.$Select.val(), sText, $TermLink, $SelectedOption;
			if ( '' === sSelected ) {
				// The blank option is a default, not a filter, and is re-selected after filtering
				return;
			}
			sText = $( '<div>' + sSelected + '</div>' ).text();
			$TermLink = $( '<a class="filter-term" href="#"></a>' ).text( sText ).click( function() {
				// Remove from current filters array
				widget.asFilters = $.grep( widget.asFilters, function( sFilter ) {
					return sFilter != sSelected;
				} );
				$TermLink.remove();
				if ( widgets.$TermContainer && 0 === widgets.$TermContainer.find( '.filter-term' ).length ) {
					widgets.$TermContainer.hide();
				}
				// Add it back to the select
				widget.$Select.append( $( '<option></option>' ).attr( 'value', sSelected ).text( sText ) );
				if ( widget.iMaxSelections > 0 && widget.iMaxSelections > widget.asFilters.length ) {
					widget.$Select.attr( 'disabled', false );
				}
				widget.fnFilter();
				return false;
			} );
			widget.asFilters.push( sSelected );
			if ( widgets.$TermContainer ) {
				widgets.$TermContainer.show();
				widgets.$TermContainer.prepend( $TermLink );
			} else {
				widget.$Select.after( $TermLink );
			}
			$SelectedOption = widget.$Select.children( 'option:selected' );
			widget.$Select.val( '' );
			$SelectedOption.remove();
			if ( widget.iMaxSelections > 0 && widget.iMaxSelections <= widget.asFilters.length ) {
				widget.$Select.attr( 'disabled', true );
			}
			widget.fnFilter();
		} );
		widget.$Container.append( widget.$Select );
		widget.fnDraw();
	};

	/**
	* Perform filtering on the target column.
	*
	* @method fnFilter
	*/
	ColumnFilterWidget.prototype.fnFilter = function() {
		var widget = this;
		var asEscapedFilters = [];
		var sFilterStart, sFilterEnd;
		if ( widget.asFilters.length > 0 ) {
			// Filters must have RegExp symbols escaped
			$.each( widget.asFilters, function( i, sFilter ) {
				asEscapedFilters.push( fnRegExpEscape( sFilter ) );
			} );
			// This regular expression filters by either whole column values or an item in a comma list
			sFilterStart = widget.sSeparator ? '(^|' + widget.sSeparator + ')(' : '^(';
			sFilterEnd = widget.sSeparator ? ')(' + widget.sSeparator + '|$)' : ')$';
			widget.oDataTable.fnFilter( sFilterStart + asEscapedFilters.join('|') + sFilterEnd, widget.iColumn, true, false );
		} else {
			// Clear any filters for this column
			widget.oDataTable.fnFilter( '', widget.iColumn );
		}
	};

	/**
	* On each table draw, update filter menu items as needed. This allows any process to
	* update the table's column visiblity and menus will still be accurate.
	*
	* @method fnDraw
	*/
	ColumnFilterWidget.prototype.fnDraw = function() {
		var widget = this;
		var oDistinctOptions = {};
		var aDistinctOptions = [];
		var aData;
		if ( widget.asFilters.length === 0 ) {
			// Find distinct column values
			aData = widget.oDataTable.fnGetColumnData( widget.iColumn );
			$.each( aData, function( i, sValue ) {
				var asValues = widget.sSeparator ? sValue.split( new RegExp( widget.sSeparator ) ) : [ sValue ];
				$.each( asValues, function( j, sOption ) {
					if ( !oDistinctOptions.hasOwnProperty( sOption ) ) {
						oDistinctOptions[sOption] = true;
						aDistinctOptions.push( sOption );
					}
				} );
			} );
			// Build the menu
			widget.$Select.empty().append( $( '<option></option>' ).attr( 'value', '' ).text( widget.oColumn.sTitle ) );
			aDistinctOptions.sort();
			$.each( aDistinctOptions, function( i, sOption ) {
				var sText;
				sText = $( '<div>' + sOption + '</div>' ).text();
				widget.$Select.append( $( '<option></option>' ).attr( 'value', sOption ).text( sText ) );
			} );
			if ( aDistinctOptions.length > 1 ) {
				// Enable the menu
				widget.$Select.attr( 'disabled', false );
			} else {
				// One option is not a useful menu, disable it
				widget.$Select.attr( 'disabled', true );
			}
		}
	};

	/*
	 * Register a new feature with DataTables
	 */
	if ( typeof $.fn.dataTable === 'function' && typeof $.fn.dataTableExt.fnVersionCheck === 'function' && $.fn.dataTableExt.fnVersionCheck('1.7.0') ) {

		$.fn.dataTableExt.aoFeatures.push( {
			'fnInit': function( oDTSettings ) {
				var oWidgets = new ColumnFilterWidgets( oDTSettings );
				return oWidgets.getContainer();
			},
			'cFeature': 'W',
			'sFeature': 'ColumnFilterWidgets'
		} );

	} else {
		throw 'Warning: ColumnFilterWidgets requires DataTables 1.7 or greater - www.datatables.net/download';
	}


}(jQuery));

