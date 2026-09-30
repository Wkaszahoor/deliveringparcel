<?php

/*
 * CMS ENDGAME 2026-09-19: the standalone Navigation builder is retired into
 * CMS Menus (admin/cms/menus, cms_menu_items). The legacy items that added
 * value (Track Order, Login, My Account) were migrated into the CMS header
 * menu; the rest were stale duplicates of CMS entries. Named redirects keep
 * old links and the old sidebar shortcut alive.
 */

Route::redirect('admin/navigation', '/admin/cms/menus', 301)->name('admin.navigation.index');
Route::redirect('admin/navigation/{any}', '/admin/cms/menus', 301)->where('any', '.*');
