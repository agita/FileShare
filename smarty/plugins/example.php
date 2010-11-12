<?php
/* vim: set ts=4 sw=4: */
// +----------------------------------------------------------------------+
// | Copyright (C) 2002-2003 Michael Yoon                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License          |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the         |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA            |
// | 02111-1307, USA.                                                     |
// +----------------------------------------------------------------------+
// | Authors: Michael Yoon <michael@yoon.org>                             |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'Smarty.class.php';

// If no date is supplied in the query string, then assume that today is the
// selected date.

if (empty($_GET['year']) || empty($_GET['month']) || empty($_GET['day'])) {
    $today = getdate();
    $year = $today['year'];
    $month = $today['mon'];
    $day = $today['mday'];
} else {
    $year = $_GET['year'];
    $month = $_GET['month'];
    $day = $_GET['day'];
}

$smarty = new Smarty;
$smarty->assign('year', $year);
$smarty->assign('month', $month);
$smarty->assign('day', $day);
$smarty->assign('url_format', '?year=%Y&month=%m&day=%d');
$smarty->display('example.tpl');
?>
