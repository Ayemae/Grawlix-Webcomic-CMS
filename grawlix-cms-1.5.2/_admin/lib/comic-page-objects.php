<?php


/*
# CHAPTER LIST

get_active_book()


Display.

function get_active_book()
function get_book_list()
function get_chapter_pages()

*/

/*

# Artist tasks

## NEW MARKER INSTANCE — done
- display_page_list ( paginated? )
- artist selects page that will *begin* new section
- on submit, create_marker ( type = chapter )

## DELETE MARKER INSTANCE — done
- display_page_list ( paginated? searchable markers? )
- OR display_marker_list()
- artist selects a marker
- on submit, delete_marker()

## MOVE MARKER INSTANCE
- display_page_list ( paginated? searchable markers? )
- artist selects marker
- artist selects new page to *begin* section
- on submit, save_marker_info()

## NEW CHAPTER (upload or not)
- display_page_list ( paginated? searchable markers? )
- artist taps a page *after* which to begin new chapter
- on tap, display create form
- artist fills out data, including after what page chapter should begin
- on submit, create_marker(type = chapter)
- IF artist uploads images, then create_pages ( after given page ID ) and upload_files()
- on submit, create_marker()

## EDIT MARKER INSTANCE (name, type, thumbnail if chapter)
- display_page_list ( paginated? searchable markers? )
- artist selects marker
- on tap, display marker editor
- artist edits info
- on submit, save_marker_info()

## MOVE PAGES
- display_page_list ( paginated? searchable markers? )
- artist taps multiple pages
- on submit, display_page_list()
- artist taps page *after* which to move pages
- move every page *after* given page ID down count ( pages_to_move )
- loop through selection, changing page sort_order to *after* ID + increment
- resort()

## NEW SINGLE PAGE
- display_page_list ( paginated? searchable markers? )
- artist taps page that will come *before* the new page
- display new page form
- on submit, create_page()
- upload_files(), assign to new page

## EDIT PAGE (title, contents, etc)
- display_page_list ( paginated? searchable markers? )
- artist taps page to edit
- display page edit form
- on submit, save_page()

## DELETE PAGE
- display_page_list ( paginated? searchable markers? )
- artist taps page(s) to delete
- delete_page()

## NEW MARKER TYPE
- display_marker_type_list()
- artist taps “new”
- display new marker form
- on submit, create_marker_type()
- display_page_list ( paginated? searchable markers? )
- prompt artist to select pages for new marker type

## EDIT MARKER TYPE
- display_marker_type_list()
- artist taps marker type to edit
- display marker type editor
- on submit, save_marker_type()
- OR when artist taps “show me marks”, display_marker_type_list() filtered by current marker type

## DELETE MARKER TYPE
- display_marker_type_list()
- artist taps marker type to delete
- delete_marker_type()
- delete_marker() of type x




# Scripts & views

page list
marker type list
search page?
marker type editor
page editor
chapter editor



# Objects

Marker view
	display_marker_list() -- form of display_page_list() ?

Comic page
	create_page()
	save_page()
	move_page() -- a form of save_page() ?

Marker
	delete_marker()
	save_marker()
	create_marker(type,position)

Marker type
	display_marker_type_list()
	create_marker_type()
	save_marker_type()
	delete_marker_type()

Book
	get_pages()
	display_page_list ( paginated? searchable? filterable by markers? )

	
*/





/* * * * * Misc */

