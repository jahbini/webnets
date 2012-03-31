<?php

/**
 * English (United Kingdom) language pack
 * @package cms
 * @subpackage i18n
 */

i18n::include_locale_file('cms', 'en_US');

global $lang;

if(array_key_exists('en_GB', $lang) && is_array($lang['en_GB'])) {
	$lang['en_GB'] = array_merge($lang['en_US'], $lang['en_GB']);
} else {
	$lang['en_GB'] = $lang['en_US'];
}

$lang['en_GB']['AssetAdmin']['CHOOSEFILE'] = 'Choose file:';
$lang['en_GB']['AssetAdmin']['DELETEDX'] = 'Deleted %s files.%s';
$lang['en_GB']['AssetAdmin']['FILESREADY'] = 'Files ready to upload:';
$lang['en_GB']['AssetAdmin']['FOLDERDELETED'] = 'folder deleted.';
$lang['en_GB']['AssetAdmin']['FOLDERSDELETED'] = 'folders deleted.';
$lang['en_GB']['AssetAdmin']['MENUTITLE'] = 'Files & Images';
$lang['en_GB']['AssetAdmin']['MENUTITLE'] = 'Files & Images';
$lang['en_GB']['AssetAdmin']['MOVEDX'] = 'Moved %s files';
$lang['en_GB']['AssetAdmin']['NEWFOLDER'] = 'NewFolder';
$lang['en_GB']['AssetAdmin']['NOTEMP'] = 'There is no temporary folder for uploads. Please set upload_tmp_dir in php.ini.';
$lang['en_GB']['AssetAdmin']['NOTHINGTOUPLOAD'] = 'There was nothing to upload';
$lang['en_GB']['AssetAdmin']['NOWBROKEN'] = 'The following pages now have broken links:';
$lang['en_GB']['AssetAdmin']['NOWBROKEN2'] = 'Their owners have been emailed and they will fix up those pages.';
$lang['en_GB']['AssetAdmin']['SAVEDFILE'] = 'Saved file %s';
$lang['en_GB']['AssetAdmin']['SAVEFOLDERNAME'] = 'Save folder name';
$lang['en_GB']['AssetAdmin']['THUMBSDELETED'] = '%s unused thumbnails have been deleted';
$lang['en_GB']['AssetAdmin']['UPLOAD'] = 'Upload Files Listed Below';
$lang['en_GB']['AssetAdmin']['UPLOADEDX'] = 'Uploaded %s files';
$lang['en_GB']['AssetAdmin_left.ss']['CREATE'] = 'Create';
$lang['en_GB']['AssetAdmin_left.ss']['DELETE'] = 'Delete';
$lang['en_GB']['AssetAdmin_left.ss']['DELFOLDERS'] = 'Delete the selected folders';
$lang['en_GB']['AssetAdmin_left.ss']['ENABLEDRAGGING'] = 'Allow drag &amp; drop reordering';
$lang['en_GB']['AssetAdmin_left.ss']['FOLDERS'] = 'Folders';
$lang['en_GB']['AssetAdmin_left.ss']['GO'] = 'Go';
$lang['en_GB']['AssetAdmin_left.ss']['SELECTTODEL'] = 'Select the folders that you want to delete and then click the button below';
$lang['en_GB']['AssetAdmin_left.ss']['TOREORG'] = 'To reorganise your folders, drag them around as desired.';
$lang['en_GB']['AssetAdmin_right.ss']['CHOOSEPAGE'] = 'Please choose a page from the left.';
$lang['en_GB']['AssetAdmin_right.ss']['WELCOME'] = 'Welcome to';
$lang['en_GB']['AssetAdmin_uploadiframe.ss']['PERMFAILED'] = 'You do not have permission to upload files into this folder.';
$lang['en_GB']['AssetTableField']['CAPTION'] = 'Caption';
$lang['en_GB']['AssetTableField']['CREATED'] = 'First uploaded';
$lang['en_GB']['AssetTableField']['DIM'] = 'Dimensions';
$lang['en_GB']['AssetTableField']['DIMLIMT'] = 'Limit The Dimensions In The Popup Window';
$lang['en_GB']['AssetTableField']['FILENAME'] = 'Filename';
$lang['en_GB']['AssetTableField']['GALLERYOPTIONS'] = 'Gallery Options';
$lang['en_GB']['AssetTableField']['IMAGE'] = 'Image';
$lang['en_GB']['AssetTableField']['ISFLASH'] = 'Is a Flash Document';
$lang['en_GB']['AssetTableField']['LASTEDIT'] = 'Last changed';
$lang['en_GB']['AssetTableField']['MAIN'] = 'Main';
$lang['en_GB']['AssetTableField']['NOLINKS'] = 'This file hasn\'t been linked to from any pages.';
$lang['en_GB']['AssetTableField']['OWNER'] = 'Owner';
$lang['en_GB']['AssetTableField']['PAGESLINKING'] = 'The following pages link to this file:';
$lang['en_GB']['AssetTableField']['POPUPHEIGHT'] = 'Popup Height';
$lang['en_GB']['AssetTableField']['POPUPWIDTH'] = 'Popup Width';
$lang['en_GB']['AssetTableField']['SIZE'] = 'Size';
$lang['en_GB']['AssetTableField.ss']['DELFILE'] = 'Delete this file';
$lang['en_GB']['AssetTableField.ss']['DRAGTOFOLDER'] = 'Drag to folder on left to move file';
$lang['en_GB']['AssetTableField.ss']['EDIT'] = 'Edit asset';
$lang['en_GB']['AssetTableField.ss']['SHOW'] = 'Show asset';
$lang['en_GB']['AssetTableField']['SWFFILEOPTIONS'] = 'SWF File Options';
$lang['en_GB']['AssetTableField']['TITLE'] = 'Title';
$lang['en_GB']['AssetTableField']['TYPE'] = 'Type';
$lang['en_GB']['AssetTableField']['URL'] = 'URL';
$lang['en_GB']['CMSMain']['ACCESS'] = 'Access to \'%s\' section';
$lang['en_GB']['CMSMain']['ACCESSALLINTERFACES'] = 'Access to all CMS sections';
$lang['en_GB']['CMSMain']['CANCEL'] = 'Cancel';
$lang['en_GB']['CMSMain']['CHOOSEREPORT'] = '(Choose a report)';
$lang['en_GB']['CMSMain']['COMPARINGV'] = 'Comparing versions %s and %s';
$lang['en_GB']['CMSMain']['COPYPUBTOSTAGE'] = 'Do you really want to copy the published content to the stage site?';
$lang['en_GB']['CMSMain']['DELETE'] = 'Delete from the draft site';
$lang['en_GB']['CMSMain']['DESCREMOVED'] = 'and %s descendants';
$lang['en_GB']['CMSMain']['EMAIL'] = 'Email';
$lang['en_GB']['CMSMain']['GO'] = 'Go';
$lang['en_GB']['CMSMain']['MENUTITLE'] = 'Site Content';
$lang['en_GB']['CMSMain']['MENUTITLE'] = 'Pages';
$lang['en_GB']['CMSMain']['METADESCOPT'] = 'Description';
$lang['en_GB']['CMSMain']['METAKEYWORDSOPT'] = 'Keywords';
$lang['en_GB']['CMSMain']['NEW'] = 'New';
$lang['en_GB']['CMSMain']['NOCONTENT'] = 'no content';
$lang['en_GB']['CMSMain']['NOTHINGASSIGNED'] = 'You have nothing assigned to you.';
$lang['en_GB']['CMSMain']['NOWAITINGON'] = 'You aren\'t waiting on anybody.';
$lang['en_GB']['CMSMain']['NOWBROKEN'] = ' The following pages now have broken links:';
$lang['en_GB']['CMSMain']['NOWBROKEN2'] = 'Their owners have been emailed and they will fix up those pages.';
$lang['en_GB']['CMSMain']['OK'] = 'OK';
$lang['en_GB']['CMSMain']['PAGEDEL'] = '%d page deleted';
$lang['en_GB']['CMSMain']['PAGENOTEXISTS'] = 'This page doesn\'t exist';
$lang['en_GB']['CMSMain']['PAGEPUB'] = '%d page published ';
$lang['en_GB']['CMSMain']['PAGESDEL'] = '%d pages deleted';
$lang['en_GB']['CMSMain']['PAGESPUB'] = '%d pages published ';
$lang['en_GB']['CMSMain']['PAGETYPEOPT'] = 'Page Type';
$lang['en_GB']['CMSMain']['PRINT'] = 'Print';
$lang['en_GB']['CMSMain']['PUBALLCONFIRM'] = 'Please publish every page in the site, copying content stage to live';
$lang['en_GB']['CMSMain']['PUBALLFUN'] = '"Publish All" functionality';
$lang['en_GB']['CMSMain']['PUBALLFUN2'] = 'Pressing this button will do the equivalent of going to every page and pressing "publish". It\'s intended to be used after there have been massive edits of the content, such as when the site was
first built.';
$lang['en_GB']['CMSMain']['PUBPAGES'] = 'Done: Published %d pages';
$lang['en_GB']['CMSMain']['REMOVED'] = 'Deleted \'%s\'%s from live site';
$lang['en_GB']['CMSMain']['REMOVEDFD'] = 'Removed from the draft site';
$lang['en_GB']['CMSMain']['REMOVEDPAGE'] = 'Removed \'%s\' from the published site';
$lang['en_GB']['CMSMain']['REMOVEDPAGEFROMDRAFT'] = 'Removed \'%s\' from the draft site';
$lang['en_GB']['CMSMain']['REPORT'] = 'Report';
$lang['en_GB']['CMSMain']['RESTORED'] = 'Restored \'%s\' successfully';
$lang['en_GB']['CMSMain']['ROLLBACK'] = 'Roll back to this version';
$lang['en_GB']['CMSMain']['ROLLEDBACKPUB'] = 'Rolled back to published version. New version number is #%d';
$lang['en_GB']['CMSMain']['ROLLEDBACKVERSION'] = 'Rolled back to version #%d. New version number is #%d';
$lang['en_GB']['CMSMain']['SAVE'] = 'Save';
$lang['en_GB']['CMSMain']['SENTTO'] = 'Sent to %s %s for approval.';
$lang['en_GB']['CMSMain']['STATUSOPT'] = 'Status';
$lang['en_GB']['CMSMain']['TOTALPAGES'] = 'Total pages:';
$lang['en_GB']['CMSMain']['VERSIONSNOPAGE'] = 'Can\'t find page #%d';
$lang['en_GB']['CMSMain']['VIEWING'] = 'You are viewing version #%s, created %s by %s';
$lang['en_GB']['CMSMain']['VISITRESTORE'] = 'visit restorepage/(ID)';
$lang['en_GB']['CMSMain']['WAITINGON'] = 'You are waiting on other people to work on these <b>%d</b> pages.';
$lang['en_GB']['CMSMain']['WORKTODO'] = 'You have work to do on these <b>%d</b> pages.';
$lang['en_GB']['CMSMain_dialog.ss']['BUTTONNOTFOUND'] = 'Couldn\'t find the button name';
$lang['en_GB']['CMSMain_dialog.ss']['NOLINKED'] = 'Can\'t find window.linkedObject to send the button click back to the main window';
$lang['en_GB']['CMSMain_left.ss']['ADDEDNOTPUB'] = 'Added to the draft site and not published yet';
$lang['en_GB']['CMSMain_left.ss']['ADDSEARCHCRITERIA'] = 'Add Criteria';
$lang['en_GB']['CMSMain_left.ss']['BATCHACTIONS'] = 'Batch Actions';
$lang['en_GB']['CMSMain_left.ss']['CHANGED'] = 'changed';
$lang['en_GB']['CMSMain_left.ss']['CLOSEBOX'] = 'click to close box';
$lang['en_GB']['CMSMain_left.ss']['COMPAREMODE'] = 'Compare mode (click 2 below)';
$lang['en_GB']['CMSMain_left.ss']['CREATE'] = 'Create';
$lang['en_GB']['CMSMain_left.ss']['DEL'] = 'deleted';
$lang['en_GB']['CMSMain_left.ss']['DELETECONFIRM'] = 'Delete the selected pages';
$lang['en_GB']['CMSMain_left.ss']['DELETEDSTILLLIVE'] = 'Deleted from the draft site but still on the live site';
$lang['en_GB']['CMSMain_left.ss']['EDITEDNOTPUB'] = 'Edited on the draft site and not published yet';
$lang['en_GB']['CMSMain_left.ss']['EDITEDSINCE'] = 'Edited Since';
$lang['en_GB']['CMSMain_left.ss']['ENABLEDRAGGING'] = 'Allow drag &amp; drop reordering';
$lang['en_GB']['CMSMain_left.ss']['GO'] = 'Go';
$lang['en_GB']['CMSMain_left.ss']['KEY'] = 'Key:';
$lang['en_GB']['CMSMain_left.ss']['NEW'] = 'new';
$lang['en_GB']['CMSMain_left.ss']['OPENBOX'] = 'click to open this box';
$lang['en_GB']['CMSMain_left.ss']['PAGEVERSIONH'] = 'Page Version History';
$lang['en_GB']['CMSMain_left.ss']['PUBLISHCONFIRM'] = 'Publish the selected pages';
$lang['en_GB']['CMSMain_left.ss']['SEARCH'] = 'Search';
$lang['en_GB']['CMSMain_left.ss']['SEARCHTITLE'] = 'Search through URL, Title, Menu Title, &amp; Content';
$lang['en_GB']['CMSMain_left.ss']['SELECTPAGESACTIONS'] = 'Select the pages that you want to change &amp; then click an action:';
$lang['en_GB']['CMSMain_left.ss']['SHOWONLYCHANGED'] = 'Show only changed pages';
$lang['en_GB']['CMSMain_left.ss']['SHOWUNPUB'] = 'Show unpublished versions';
$lang['en_GB']['CMSMain_left.ss']['SITECONTENT TITLE'] = 'Page Tree';
$lang['en_GB']['CMSMain_left.ss']['SITEREPORTS'] = 'Site Reports';
$lang['en_GB']['CMSMain_right.ss']['ANYMESSAGE'] = 'Do you have any message for your editor?';
$lang['en_GB']['CMSMain_right.ss']['CHOOSEPAGE'] = 'Please choose a page from the left.';
$lang['en_GB']['CMSMain_right.ss']['LOADING'] = 'loading...';
$lang['en_GB']['CMSMain_right.ss']['MESSAGE'] = 'Message';
$lang['en_GB']['CMSMain_right.ss']['SENDTO'] = 'Send to';
$lang['en_GB']['CMSMain_right.ss']['STATUS'] = 'Status';
$lang['en_GB']['CMSMain_right.ss']['SUBMIT'] = 'Submit for approval';
$lang['en_GB']['CMSMain_right.ss']['WELCOMETO'] = 'Welcome to';
$lang['en_GB']['CMSMain_versions.ss']['AUTHOR'] = 'User';
$lang['en_GB']['CMSMain_versions.ss']['NOTPUB'] = 'Not published';
$lang['en_GB']['CMSMain_versions.ss']['PUBR'] = 'Publisher';
$lang['en_GB']['CMSMain_versions.ss']['UNKNOWN'] = 'Unknown';
$lang['en_GB']['CMSMain_versions.ss']['WHEN'] = 'When';
$lang['en_GB']['CommentAdmin']['ACCEPT'] = 'Accept';
$lang['en_GB']['CommentAdmin']['APPROVED'] = 'Accepted %s comments.';
$lang['en_GB']['CommentAdmin']['APPROVEDCOMMENTS'] = 'Approved Comments';
$lang['en_GB']['CommentAdmin']['AUTHOR'] = 'Author';
$lang['en_GB']['CommentAdmin']['COMMENT'] = 'Comment';
$lang['en_GB']['CommentAdmin']['COMMENTS'] = 'Comments';
$lang['en_GB']['CommentAdmin']['COMMENTSAWAITINGMODERATION'] = 'Comments Awaiting Moderation';
$lang['en_GB']['CommentAdmin']['DATEPOSTED'] = 'Date Posted';
$lang['en_GB']['CommentAdmin']['DELETE'] = 'Delete';
$lang['en_GB']['CommentAdmin']['DELETEALL'] = 'Delete All';
$lang['en_GB']['CommentAdmin']['DELETED'] = 'Deleted %s comments.';
$lang['en_GB']['CommentAdmin']['MARKASNOTSPAM'] = 'Mark as not SPAM';
$lang['en_GB']['CommentAdmin']['MARKEDNOTSPAM'] = 'Marked %s comments as not SPAM.';
$lang['en_GB']['CommentAdmin']['MARKEDSPAM'] = 'Marked %s comments as SPAM.';
$lang['en_GB']['CommentAdmin']['MENUTITLE'] = 'Comments';
$lang['en_GB']['CommentAdmin']['MENUTITLE'] = 'Comments';
$lang['en_GB']['CommentAdmin']['NAME'] = 'Name';
$lang['en_GB']['CommentAdmin']['PAGE'] = 'Page';
$lang['en_GB']['CommentAdmin']['SPAM'] = 'SPAM';
$lang['en_GB']['CommentAdmin']['SPAMMARKED'] = 'Mark as SPAM';
$lang['en_GB']['CommentAdmin_left.ss']['COMMENTS'] = 'Comments';
$lang['en_GB']['CommentAdmin_right.ss']['WELCOME1'] = 'Welcome to the';
$lang['en_GB']['CommentAdmin_right.ss']['WELCOME2'] = 'comment management. Please select a folder in the tree on the left.';
$lang['en_GB']['CommentAdmin_SiteTree.ss']['APPROVED'] = 'Approved';
$lang['en_GB']['CommentAdmin_SiteTree.ss']['AWAITMODERATION'] = 'Awaiting Moderation';
$lang['en_GB']['CommentAdmin_SiteTree.ss']['COMMENTS'] = 'Comments';
$lang['en_GB']['CommentAdmin_SiteTree.ss']['SPAM'] = 'SPAM';
$lang['en_GB']['CommentList.ss']['CREATEDW'] = 'Comments are created whenever one of the \'workflow actions\'
are undertaken - Publish, Reject, Submit.';
$lang['en_GB']['CommentList.ss']['NOCOM'] = 'There are no comments on this page.';
$lang['en_GB']['CommentTableField']['FILTER'] = 'Filter';
$lang['en_GB']['CommentTableField']['SEARCH'] = 'Search';
$lang['en_GB']['CommentTableField.ss']['APPROVE'] = 'approve';
$lang['en_GB']['CommentTableField.ss']['APPROVECOMMENT'] = 'Approve this comment';
$lang['en_GB']['CommentTableField.ss']['DELETE'] = 'delete';
$lang['en_GB']['CommentTableField.ss']['DELETEROW'] = 'Delete this row';
$lang['en_GB']['CommentTableField.ss']['EDIT'] = 'edit';
$lang['en_GB']['CommentTableField.ss']['HAM'] = 'ham';
$lang['en_GB']['CommentTableField.ss']['MARKASSPAM'] = 'Mark this comment as SPAM';
$lang['en_GB']['CommentTableField.ss']['MARKNOSPAM'] = 'Mark this comment as not SPAM';
$lang['en_GB']['CommentTableField.ss']['NOITEMSFOUND'] = 'No items found';
$lang['en_GB']['CommentTableField.ss']['SPAM'] = 'SPAM';
$lang['en_GB']['ComplexTableField']['CLOSEPOPUP'] = 'Close Popup';
$lang['en_GB']['ComplexTableField']['SUCCESSADD'] = 'Added %s %s %s';
$lang['en_GB']['ImageEditor.ss']['ACTIONS'] = 'actions';
$lang['en_GB']['ImageEditor.ss']['ADJUST'] = 'adjust';
$lang['en_GB']['ImageEditor.ss']['APPLY'] = 'apply';
$lang['en_GB']['ImageEditor.ss']['BLUR'] = 'blur';
$lang['en_GB']['ImageEditor.ss']['BRIGHTNESS'] = 'brightness';
$lang['en_GB']['ImageEditor.ss']['CANCEL'] = 'cancel';
$lang['en_GB']['ImageEditor.ss']['CONTRAST'] = 'contrast';
$lang['en_GB']['ImageEditor.ss']['CROP'] = 'crop';
$lang['en_GB']['ImageEditor.ss']['CURRENTACTION'] = 'current&nbsp;action';
$lang['en_GB']['ImageEditor.ss']['EDITFUNCTIONS'] = 'edit&nbsp;functions';
$lang['en_GB']['ImageEditor.ss']['EFFECTS'] = 'effects';
$lang['en_GB']['ImageEditor.ss']['EXIT'] = 'exit';
$lang['en_GB']['ImageEditor.ss']['GAMMA'] = 'gamma';
$lang['en_GB']['ImageEditor.ss']['GREYSCALE'] = 'greyscale';
$lang['en_GB']['ImageEditor.ss']['HEIGHT'] = 'height';
$lang['en_GB']['ImageEditor.ss']['REDO'] = 'redo';
$lang['en_GB']['ImageEditor.ss']['ROT'] = 'rotate';
$lang['en_GB']['ImageEditor.ss']['SAVE'] = 'save&nbsp;image';
$lang['en_GB']['ImageEditor.ss']['SEPIA'] = 'sepia';
$lang['en_GB']['ImageEditor.ss']['UNDO'] = 'undo';
$lang['en_GB']['ImageEditor.ss']['UNTITLED'] = 'Untitled Document';
$lang['en_GB']['ImageEditor.ss']['WIDTH'] = 'width';
$lang['en_GB']['LeftAndMain']['CHANGEDURL'] = 'Changed URL to \'%s\'';
$lang['en_GB']['LeftAndMain']['COMMENTS'] = 'Comments';
$lang['en_GB']['LeftAndMain']['FILESIMAGES'] = 'Files & Images';
$lang['en_GB']['LeftAndMain']['HELP'] = 'Help';
$lang['en_GB']['LeftAndMain']['PAGETYPE'] = 'Page type:';
$lang['en_GB']['LeftAndMain']['PERMAGAIN'] = 'You have been logged out of the CMS. If you would like to log in again, enter a username and password below.';
$lang['en_GB']['LeftAndMain']['PERMALREADY'] = 'I\'m sorry, but you can\'t access that part of the CMS. If you want to log in as someone else, do so below';
$lang['en_GB']['LeftAndMain']['PERMDEFAULT'] = 'Please choose an authentication method and enter your credentials to access the CMS.';
$lang['en_GB']['LeftAndMain']['PLEASESAVE'] = 'Please Save Page: This page could not be updated because it hasn\'t been saved yet.';
$lang['en_GB']['LeftAndMain']['REPORTS'] = 'Reports';
$lang['en_GB']['LeftAndMain']['REQUESTERROR'] = 'Error in request';
$lang['en_GB']['LeftAndMain']['SAVED'] = 'saved';
$lang['en_GB']['LeftAndMain']['SAVEDUP'] = 'Saved';
$lang['en_GB']['LeftAndMain']['SECURITY'] = 'Security';
$lang['en_GB']['LeftAndMain']['SITECONTENT'] = 'Site Content';
$lang['en_GB']['LeftAndMain']['SITECONTENTLEFT'] = 'Site Content';
$lang['en_GB']['LeftAndMain.ss']['APPVERSIONTEXT1'] = 'This is the';
$lang['en_GB']['LeftAndMain.ss']['APPVERSIONTEXT2'] = 'version that you are currently running, technically it\'s the CVS branch';
$lang['en_GB']['LeftAndMain.ss']['ARCHS'] = 'Archived Site';
$lang['en_GB']['LeftAndMain.ss']['DRAFTS'] = 'Draft Site';
$lang['en_GB']['LeftAndMain.ss']['EDIT'] = 'Edit';
$lang['en_GB']['LeftAndMain.ss']['EDITINCMS'] = 'Edit this page in the CMS';
$lang['en_GB']['LeftAndMain.ss']['EDITPROFILE'] = 'Profile';
$lang['en_GB']['LeftAndMain.ss']['LOADING'] = 'Loading...';
$lang['en_GB']['LeftAndMain.ss']['LOGGEDINAS'] = 'Logged in as';
$lang['en_GB']['LeftAndMain.ss']['LOGOUT'] = 'Log out';
$lang['en_GB']['LeftAndMain.ss']['PUBLIS'] = 'Published Site';
$lang['en_GB']['LeftAndMain.ss']['REQUIREJS'] = 'The CMS requires that you have JavaScript enabled.';
$lang['en_GB']['LeftAndMain.ss']['SSWEB'] = 'Silverstripe Website';
$lang['en_GB']['LeftAndMain.ss']['VIEWINDRAFT'] = 'View the Page in the Draft Site';
$lang['en_GB']['LeftAndMain.ss']['VIEWINPUBLISHED'] = 'View the Page in the Published Site';
$lang['en_GB']['LeftAndMain.ss']['VIEWPAGEIN'] = 'Page view:';
$lang['en_GB']['LeftAndMain']['STATUSPUBLISHEDSUCCESS'] = 'Published \'%s\' successfully';
$lang['en_GB']['LeftAndMain']['STATUSTO'] = ' Status changed to \'%s\'';
$lang['en_GB']['LeftAndMain']['TREESITECONTENT'] = 'Site Content';
$lang['en_GB']['MemberList.ss']['FILTER'] = 'Filter';
$lang['en_GB']['MemberList_PageControls.ss']['DISPLAYING'] = 'Displaying';
$lang['en_GB']['MemberList_PageControls.ss']['FIRSTMEMBERS'] = 'members';
$lang['en_GB']['MemberList_PageControls.ss']['LASTMEMBERS'] = 'members';
$lang['en_GB']['MemberList_PageControls.ss']['NEXTMEMBERS'] = 'members';
$lang['en_GB']['MemberList_PageControls.ss']['OF'] = 'of';
$lang['en_GB']['MemberList_PageControls.ss']['PREVIOUSMEMBERS'] = 'members';
$lang['en_GB']['MemberList_PageControls.ss']['TO'] = 'to';
$lang['en_GB']['MemberList_PageControls.ss']['VIEWFIRST'] = 'View first';
$lang['en_GB']['MemberList_PageControls.ss']['VIEWLAST'] = 'View last';
$lang['en_GB']['MemberList_PageControls.ss']['VIEWNEXT'] = 'View next';
$lang['en_GB']['MemberList_PageControls.ss']['VIEWPREVIOUS'] = 'View previous';
$lang['en_GB']['MemberList_Table.ss']['EMAIL'] = 'Email Address';
$lang['en_GB']['MemberList_Table.ss']['FN'] = 'First Name';
$lang['en_GB']['MemberList_Table.ss']['PASSWD'] = 'Password';
$lang['en_GB']['MemberList_Table.ss']['SN'] = 'Surname';
$lang['en_GB']['MemberTableField']['ADD'] = 'Add';
$lang['en_GB']['MemberTableField']['ADDEDTOGROUP'] = 'Added member to group';
$lang['en_GB']['MemberTableField']['ADDINGFIELD'] = 'Adding failed';
$lang['en_GB']['MemberTableField']['ANYGROUP'] = 'Any group';
$lang['en_GB']['MemberTableField']['ASC'] = 'Ascending';
$lang['en_GB']['MemberTableField']['DESC'] = 'Descending';
$lang['en_GB']['MemberTableField']['EMAIL'] = 'Email';
$lang['en_GB']['MemberTableField']['FILTER'] = 'Filter';
$lang['en_GB']['MemberTableField']['FILTERBYGROUP'] = 'Filter by group';
$lang['en_GB']['MemberTableField']['FIRSTNAME'] = 'FirstName';
$lang['en_GB']['MemberTableField']['ORDERBY'] = 'Order by';
$lang['en_GB']['MemberTableField']['SEARCH'] = 'Search';
$lang['en_GB']['MemberTableField.ss']['ADDNEW'] = 'Add new';
$lang['en_GB']['MemberTableField.ss']['DELETEMEMBER'] = 'Delete this member';
$lang['en_GB']['MemberTableField.ss']['EDITMEMBER'] = 'Edit this member';
$lang['en_GB']['MemberTableField.ss']['SHOWMEMBER'] = 'Show this member';
$lang['en_GB']['MemberTableField']['SURNAME'] = 'Surname';
$lang['en_GB']['ModelAdmin']['ADDBUTTON'] = 'Add';
$lang['en_GB']['ModelAdmin']['ADDFORM'] = 'Fill out this form to add a %s to the database.';
$lang['en_GB']['ModelAdmin']['CHOOSE_COLUMNS'] = 'Select result columns...';
$lang['en_GB']['ModelAdmin']['CLEAR_SEARCH'] = 'Clear Search';
$lang['en_GB']['ModelAdmin']['CREATEBUTTON'] = 'Create \'%s\'';
$lang['en_GB']['ModelAdmin']['DELETE'] = 'Delete';
$lang['en_GB']['ModelAdmin']['DELETEDRECORDS'] = 'Deleted %s records.';
$lang['en_GB']['ModelAdmin']['FOUNDRESULTS'] = 'Your search found %s matching items';
$lang['en_GB']['ModelAdmin']['GOBACK'] = 'Back';
$lang['en_GB']['ModelAdmin']['GOFORWARD'] = 'Forward';
$lang['en_GB']['ModelAdmin']['IMPORT'] = 'Import from CSV';
$lang['en_GB']['ModelAdmin']['IMPORTEDRECORDS'] = 'Imported %s records.';
$lang['en_GB']['ModelAdmin']['ITEMNOTFOUND'] = 'That item was not found.';
$lang['en_GB']['ModelAdmin']['LOADEDFOREDITING'] = 'Loaded \'%s\' for editing.';
$lang['en_GB']['ModelAdmin']['NOCSVFILE'] = 'Please browse for a CSV file to import';
$lang['en_GB']['ModelAdmin']['NOIMPORT'] = 'Nothing to import';
$lang['en_GB']['ModelAdmin']['NORESULTS'] = 'No results';
$lang['en_GB']['ModelAdmin']['SAVE'] = 'Save';
$lang['en_GB']['ModelAdmin']['SEARCHRESULTS'] = 'Search Results';
$lang['en_GB']['ModelAdmin']['SELECTALL'] = 'select all';
$lang['en_GB']['ModelAdmin']['SELECTNONE'] = 'select none';
$lang['en_GB']['ModelAdmin']['UPDATEDRECORDS'] = 'Updated %s records.';
$lang['en_GB']['ModelAdmin_ImportSpec.ss']['IMPORTSPECFIELDS'] = 'Database columns';
$lang['en_GB']['ModelAdmin_ImportSpec.ss']['IMPORTSPECLINK'] = 'Show Specification for %s';
$lang['en_GB']['ModelAdmin_ImportSpec.ss']['IMPORTSPECRELATIONS'] = 'Relations';
$lang['en_GB']['ModelAdmin_ImportSpec.ss']['IMPORTSPECTITLE'] = 'Specification for %s';
$lang['en_GB']['ModelAdmin_left.ss']['ADDLISTING'] = 'Add';
$lang['en_GB']['ModelAdmin_left.ss']['IMPORT_TAB_HEADER'] = 'Import';
$lang['en_GB']['ModelAdmin_left.ss']['SEARCHLISTINGS'] = 'Search';
$lang['en_GB']['ModelAdmin_right.ss']['WELCOME1'] = 'Welcome to %s. Please choose on one of the entries in the left pane.';
$lang['en_GB']['PageComment']['Comment'] = 'Comment';
$lang['en_GB']['PageComment']['COMMENTBY'] = 'Comment by \'%s\' on %s';
$lang['en_GB']['PageComment']['IsSpam'] = 'Spam?';
$lang['en_GB']['PageComment']['Name'] = 'Author Name';
$lang['en_GB']['PageComment']['NeedsModeration'] = 'Needs Moderation?';
$lang['en_GB']['PageComment']['PLURALNAME'] = 'Page Comments';
$lang['en_GB']['PageComment']['SINGULARNAME'] = 'Page Comment';
$lang['en_GB']['PageCommentInterface']['COMMENTERURL'] = 'Your website URL';
$lang['en_GB']['PageCommentInterface']['POST'] = 'Post';
$lang['en_GB']['PageCommentInterface']['SPAMQUESTION'] = 'SPAM protection question: %s';
$lang['en_GB']['PageCommentInterface.ss']['COMMENTS'] = 'Comments';
$lang['en_GB']['PageCommentInterface.ss']['NEXT'] = 'next';
$lang['en_GB']['PageCommentInterface.ss']['NOCOMMENTSYET'] = 'No one has commented on this page yet.';
$lang['en_GB']['PageCommentInterface.ss']['POSTCOM'] = 'Post your comment';
$lang['en_GB']['PageCommentInterface.ss']['PREV'] = 'previous';
$lang['en_GB']['PageCommentInterface.ss']['RSSFEEDALLCOMMENTS'] = 'RSS feed for all comments';
$lang['en_GB']['PageCommentInterface.ss']['RSSFEEDCOMMENTS'] = 'RSS feed for comments on this page';
$lang['en_GB']['PageCommentInterface.ss']['RSSVIEWALLCOMMENTS'] = 'View all Comments';
$lang['en_GB']['PageCommentInterface']['YOURCOMMENT'] = 'Comments';
$lang['en_GB']['PageCommentInterface']['YOURNAME'] = 'Your name';
$lang['en_GB']['PageCommentInterface_Controller']['SPAMQUESTION'] = 'SPAM protection question: %s';
$lang['en_GB']['PageCommentInterface_Form']['AWAITINGMODERATION'] = 'Your comment has been submitted and is now awaiting moderation.';
$lang['en_GB']['PageCommentInterface_Form']['MSGYOUPOSTED'] = 'The message you posted was:';
$lang['en_GB']['PageCommentInterface_Form']['SPAMDETECTED'] = 'SPAM detected!!';
$lang['en_GB']['PageCommentInterface_singlecomment.ss']['APPROVE'] = 'approve this comment';
$lang['en_GB']['PageCommentInterface_singlecomment.ss']['ISNTSPAM'] = 'this comment is not spam';
$lang['en_GB']['PageCommentInterface_singlecomment.ss']['ISSPAM'] = 'this comment is spam';
$lang['en_GB']['PageCommentInterface_singlecomment.ss']['PBY'] = 'Posted by';
$lang['en_GB']['PageCommentInterface_singlecomment.ss']['REMCOM'] = 'remove this comment';
$lang['en_GB']['ReportAdmin']['MENUTITLE'] = 'Reports';
$lang['en_GB']['ReportAdmin_left.ss']['REPORTS'] = 'Reports';
$lang['en_GB']['ReportAdmin_right.ss']['WELCOME1'] = 'Welcome to the';
$lang['en_GB']['ReportAdmin_right.ss']['WELCOME2'] = 'reporting section. Please choose a specific report from the left.';
$lang['en_GB']['ReportAdmin_SiteTree.ss']['REPORTS'] = 'Reports';
$lang['en_GB']['SecurityAdmin']['ADDMEMBER'] = 'Add Member';
$lang['en_GB']['SecurityAdmin']['EDITPERMISSIONS'] = 'Manage permissions for groups';
$lang['en_GB']['SecurityAdmin']['MENUTITLE'] = 'Security';
$lang['en_GB']['SecurityAdmin']['MENUTITLE'] = 'Security';
$lang['en_GB']['SecurityAdmin']['NEWGROUP'] = 'New Group';
$lang['en_GB']['SecurityAdmin']['SAVE'] = 'Save';
$lang['en_GB']['SecurityAdmin']['SGROUPS'] = 'Security Groups';
$lang['en_GB']['SecurityAdmin_left.ss']['CREATE'] = 'Create';
$lang['en_GB']['SecurityAdmin_left.ss']['DEL'] = 'Delete';
$lang['en_GB']['SecurityAdmin_left.ss']['DELGROUPS'] = 'Delete the selected groups';
$lang['en_GB']['SecurityAdmin_left.ss']['ENABLEDRAGGING'] = 'Allow drag &amp; drop reordering';
$lang['en_GB']['SecurityAdmin_left.ss']['GO'] = 'Go';
$lang['en_GB']['SecurityAdmin_left.ss']['SECGROUPS'] = 'Security Groups';
$lang['en_GB']['SecurityAdmin_left.ss']['SELECT'] = 'Select the pages that you want to delete and then click the button below';
$lang['en_GB']['SecurityAdmin_left.ss']['TOREORG'] = 'To reorganise your site, drag the pages around as desired.';
$lang['en_GB']['SecurityAdmin_right.ss']['WELCOME1'] = 'Welcome to the';
$lang['en_GB']['SecurityAdmin_right.ss']['WELCOME2'] = 'security administration section. Please choose a group from the left.';
$lang['en_GB']['SideReport']['EMPTYPAGES'] = 'Pages with no content';
$lang['en_GB']['SideReport']['LAST2WEEKS'] = 'Pages edited in the last 2 weeks';
$lang['en_GB']['SideReport']['REPEMPTY'] = 'The %s report is empty.';
$lang['en_GB']['SideReport']['TODO'] = 'Pages with To Do items';
$lang['en_GB']['StaticExporter']['BASEURL'] = 'Base URL';
$lang['en_GB']['StaticExporter']['EXPORTTO'] = 'Export to that folder';
$lang['en_GB']['StaticExporter']['FOLDEREXPORT'] = 'Folder to export to';
$lang['en_GB']['StaticExporter']['NAME'] = 'Static exporter';
$lang['en_GB']['TableListField_PageControls.ss']['DISPLAYING'] = 'Displaying';
$lang['en_GB']['TableListField_PageControls.ss']['OF'] = 'of';
$lang['en_GB']['TableListField_PageControls.ss']['TO'] = 'to';
$lang['en_GB']['TableListField_PageControls.ss']['VIEWFIRST'] = 'View first';
$lang['en_GB']['TableListField_PageControls.ss']['VIEWLAST'] = 'View last';
$lang['en_GB']['TableListField_PageControls.ss']['VIEWNEXT'] = 'View next';
$lang['en_GB']['TableListField_PageControls.ss']['VIEWPREVIOUS'] = 'View previous';
$lang['en_GB']['ThumbnailStripField']['NOFLASHFOUND'] = 'No flash files found';
$lang['en_GB']['ThumbnailStripField']['NOFOLDERFLASHFOUND'] = 'No flash files found in';
$lang['en_GB']['ThumbnailStripField']['NOFOLDERIMAGESFOUND'] = 'No images found in';
$lang['en_GB']['ThumbnailStripField']['NOIMAGESFOUND'] = 'No images found';
$lang['en_GB']['ThumbnailStripField.ss']['CHOOSEFOLDER'] = '(Choose a folder or search above)';
$lang['en_GB']['ViewArchivedEmail.ss']['CANACCESS'] = 'You can access the archived site at this link:';
$lang['en_GB']['ViewArchivedEmail.ss']['HAVEASKED'] = 'You have asked to view the content of our site on';
$lang['en_GB']['WaitingOn.ss']['ATO'] = 'assigned to';
$lang['en_GB']['WidgetAreaEditor.ss']['AVAILABLE'] = 'Available Widgets';
$lang['en_GB']['WidgetAreaEditor.ss']['INUSE'] = 'Widgets currently used';
$lang['en_GB']['WidgetAreaEditor.ss']['NOAVAIL'] = 'There are currently no widgets available.';
$lang['en_GB']['WidgetAreaEditor.ss']['TOADD'] = 'To add widgets, drag them from the left area to here.';
$lang['en_GB']['WidgetEditor.ss']['DELETE'] = 'Delete';

