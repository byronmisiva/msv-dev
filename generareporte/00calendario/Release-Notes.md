# Extensible 1.0.x Release Notes

## 1.0.2

_February 6, 2012_

**New Features**

* Added `timeFormat` config to `DateRangeField`
* Added new Italian locale
* Added Extensible-config.js for easier example path configuration (they now work out of the box by default)

**Bugs Fixed**

* Fix MonthDetailView to honor the enableContextMenu config
* Fix event hover visual glitch in IE
* Fixed resizing an event to midnight in the day view causing invalid event times
* Enforce complete and case-sensitive event id matching when searching the event store
* Added `DropZone.destroy()` to properly remove drag/drop shim elements
* Default all views to `hideMode: offsets` to preserve scroll position cross-browser
* Fixed logic that sets the active view and navigation state on initial load
* Fixed layout under Mac OSX Lion, which changed scrollbar dimensions
* Refactored event rendering in month view to fix issues with some complex overlapping scenarios
* Fixed an edge case that could cause certain events not to show up in the Day view header
* Added null check to avoid errors when calculating the event rendering grid if the maxEvents array is empty
* Fix calendar start date not getting set correctly in some cases
* Added safety checks around all Ext overrides to avoid errors if any classes are not present

## 1.0.1

_June 23, 2011_

**New Features**

* Added locales: German, Croatian, Czech, Chinese (traditional and simplified)
* Added `Ext.ensible.Date.isWeekend()` and `Ext.ensible.Date.isWeekday()`
* Added support for custom weekend day styles in calendar views
* New calendar view configs for controlling special day styles more easily (`weekendCls`, `prevMonthCls`, `nextMonthCls` and `todayCls`)
* New _experimental_ calendar examples (not linked from the main examples page, but available under /examples/calendar/experimental)

**Bugs Fixed**

* Fixed bug that could cause the times shown in the gutter of the day view to be wrong the day after a daylight savings change
* Fixed dayNames override in Portuguese-BR locale
* Updated all locale files to use pluralization functions instead of strings for certain attributes (primarily to support Czech properly)
* Updated DayView to fire the `beforeeventresize` event with start and end date arguments
* Fix for event handling code accessing the active calendar view on date change to get the correct view
* Updated the CSS rules for the ColorPalette so that it works correctly when used outside of the calendar list menu
* Updated view templates to avoid an Ext regression bug when moving to the Ext 4.0 version of XTemplate
* Fixed several minor offset bugs in calendar drag/drop, including the initial lag when dragging that could offset the pointer incorrectly

## 1.0

_March 1, 2011_

**New Features**

* Updated the calendar + scheduler combo example

**Bugs Fixed**

* Changed `recurrenceLabelText` to `repeatsLabelText` in locale files to match code
* Fix CalendarPanel to properly relay the view's `beforeeventdelete` event
* Fix so that file order is not important when declaring classes in the `Ext.ensible.sample` namespace
* Updates to `MemoryEventStore` to handle batched records and initial record phantom status
* Fix null error on remote calendar list store load
* Fixed issues related to store `autoSave` support
* Fix intermittent null error on event drag when browser is slow to respond
* Various minor fixes related to managing event data during remote save/errors
* Remove extra body top border added by Ext when there is no panel header

## 1.0 (RC 2)

_Feb. 20, 2011_

**New Features**

* New TabPanel example
* Added Polish locale
* New CalendarView configs `dateParamStart` &amp; `dateParamEnd` to allow overriding the param names sent for read requests
* Switched CalendarView `getStoreParams`, `reloadStore` and `refresh` methods from private to public/documented
* Added new CalendarView `getStoreDateParams` method to separate default date params from custom param logic
* The CalendarCombo widget now defaults to `selectOnFocus:true`
* Enabled context menu support on "More events" popup panel
* Added default CRUD user messaging to all the examples

**Bugs Fixed**

* Case typo in event rowspan code that caused problems in Chrome
* "More events" window height grows after each display
* Improved logic that calculates size for the "more events" link
* Drag-to-create start position bug in day/week views
* CalendarCombo invalid value could break the example application
* Double POST after remote save fails
* Disable store autoSave if set so that the calendar can properly manage saving instead
* MemoryProxy override fix to always process the callback fn after any request
* Switched store logic to handle 'write' event instead of individual CRUD events for proper remote response handling
* PHP 'fail' test flag (specific to the remote sample) not working for DELETE transactions in some cases
* DomHelpr override for IE9's apparent removal of `createContextualFragment` which breaks everything (general to Ext also)
* Switched all examples to HTML5 doc type to default to standards mode rendering (mainly for IE)
* Drag offset bug when dragging quickly to move an event
* Display offset bug in setting the calendar view bounds when startDay > 0

## 1.0 (RC 1)

_Jan. 26, 2011_

**New Features**

* Lots of new options to customize the time increments and boundaries that can be shown (`showHourSeparator`, `viewStartHour`, `viewEndHour`, `scrollStartHour`, `hourHeight`, `ddIncrement`, `minEventDisplayMinutes`). This enables highly customizable calendar layouts, as shown in the new sample custom-views.html.
* New option `enableEditDetails` to hide/show the "edit details" link on the edit window
* New sample doc-types.php for easily testing various HTML doctype combinations
* Catalan and Spanish (Spain) locale files

**Bugs Fixed**

* Day/week header does not display when using many doctype combinations
* Removed store override that could break other data bound components that rely on phantom record support
* Click on week view all day header with no events present always shows first date of week in edit window
* "More events" popup window should exclude events from hidden calendars
* `CalendarList.radioCalendar` causes many unnecessary store.load calls
* Event store reloaded improperly when `view.refresh` is passed as a callback function
* String calendar ids should be allowed
* End date boundary bug in `isEventVisible`
* Revised event overlap logic for day/week views to support minimum event display height
* WeekView sometimes stays on the same week when moving forward/back using the arrow buttons
* MemoryEventStore's add event listener gets overwritten when other listeners are added by external code (applies to samples only)

## 1.0 (Beta 2)

_Dec. 30, 2010_

**New Features**

* Added CalendarView.getEventClass for customizing events at render time (works like GridView.getRowClass)
* Added Swedish, Portuguese (Portugal) and Portuguese (Brazil) locales
* Added Windows .bat build script

**Bugs Fixed**

* EventMappings not followed correctly on DayView click to show editor
* Midnight boundary display bug in DayView
* DayView/WeekView container scroll broken
* Extraneous GET request after update from edit form
* CalendarMappings not applied correctly in CalendarCombo
* Event `eventupdate` not fired correctly from views
* CalendarView.activeView undefined on initial render
* DD shims not showing on window sample (z-index issue)
* `rangeselect` event args inconsistent
* Layout bug when starting with day or week view active

## 1.0 (Beta 1)

_Dec. 9, 2010_

**API Changes**

* The default value for `CalendarView.dateParamFormat` is now 'Y-m-d' (instead of 'm-d-Y'). This was done for consistency and also because it is a more sensible default. If you are currently using the default format and handling it on your server you may need to either set this config to the old value or change the date format expected on the back end to match 'Y-m-d'.

**New Features**

* Full localization support + new sample with 4 locales
* Full remote error handling support (fully tested using the DataWriter API)
* Basic read-only calendar support
* New config to enable/disable event resizing in day/week view
* New config option to enable event editor window as modal
* New config to force a static startDay in the MultiDayView to allow for custom views

**Bugs Fixed**

* Calendar display bug for events spanning sat-sun in some cases
* Multiple GET requests on day/week view load
* Initial page load no longer executes GET with no date params
* Selected calendar date now persists consistently across page navigation
* Overlapping event display bug in day/week view for events < 30 minute duration
* Update event via DnD causes multiple duplicate PUTs
* startDay != 0 causes several boundary display bugs
* Replaced HTML char entities with XHTML-compliant codes
* Missing rounded corners on events in IE
* Null error after setting showTodayText = false

## 1.0 (Alpha 2)

_Oct. 4, 2010_

**New Features**

* Multi-calendar support
* Calendar selection sidebar widget
* CalendarListMenu component
* Remote calendar implementation example (PHP)
* New sample for showing calendar in an Ext.Window

**Bugs Fixed**

* Clicks in day header not showing edit window
* Propagate calendarStore to views correctly
* Renamed CalendarPicker -> CalendarCombo for consistency
* Various minor bug fixes and doc updates

## 1.0 (Alpha 1)

_Sept. 13, 2010_

* Original release