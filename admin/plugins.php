<?php
/**
 * MyBB 1.0
 * Copyright � 2005 MyBulletinBoard Group, All Rights Reserved
 *
 * Website: http://www.mybboard.com
 * License: http://www.mybboard.com/eula.html
 *
 * $Id$
 */

 // Do NOT load any plugins on this page
define("NO_PLUGINS", 1);

require "./global.php";

// Load language packs for this section
$lang->load("plugins");

addacpnav($lang->nav_plugins, "plugins.php");

checkadminpermissions("caneditsettings");
logadmin();

//
// Read the plugins cache
//
$plugins_cache = $cache->read("plugins");
$active_plugins = $plugins_cache['active'];

//
// Activates or deactivates a specific plugin
//
if($mybb->input['action'] == "activate")
{
	$codename = $mybb->input['plugin'];
	$file = basename($codename.".php");
	if($mybb->input['activate'])
	{
		$active_plugins[$codename] = $codename;
		$userfunc = $codename."_activate";
		$message = $lang->plugin_activated;
	}
	elseif($mybb->input['deactivate'])
	{
		unset($active_plugins[$codename]);
		$userfunc = $codename."_deactivate";
		$message = $lang->plugin_deactivated;
	}

	// Check if the file exists and throw an error if it doesn't
	if(!file_exists("./inc/plugins/$file"))
	{
		cperror($lang->plugin_not_found);
	}

	include "./inc/plugins/$file";

	//
	// If this plugin has an activate/deactivate function then run it
	//
	if(function_exists($userfunc))
	{
		$userfunc();
	}

	//
	// Update plugin cache
	//
	$plugins_cache['active'] = $active_plugins;
	$cache->update("plugins", $plugins_cache);

	cpredirect("plugins.php", $message);
}

if($mybb->input['action'] == "")
{
	//
	// Get a list of the plugin files which exist in the plugins directory
	//
	$dir = @opendir("./inc/plugins/");
	if($dir)
	{
		while($file = readdir($dir))
		{
			$ext = getextension($file);
			if($ext == "php")
			{
				$plugins_list[] = $file;
			}
		}
		@sort($plugins_list);
	}
	@closedir($dir);

	cpheader();
	starttable();
	tableheader($lang->plugin_manager, "", 4);
	echo "<tr>\n";
	echo "<td class=\"subheader\">$lang->plugin</td>\n";
	echo "<td class=\"subheader\" align=\"center\">$lang->version</td>\n";
	echo "<td class=\"subheader\" align=\"center\">$lang->author</td>\n";
	echo "<td class=\"subheader\" align=\"center\">$lang->action</td>\n";
	echo "</tr>\n";
	if($plugins_list)
	{
		foreach($plugins_list as $plugin_file)
		{
			include "./inc/plugins/".$plugin_file;
			$codename = str_replace(".php", "", $plugin_file);
			$infofunc = $codename."_info";
			if(!function_exists($infofunc))
			{
				continue;
			}
			$plugininfo = $infofunc();
			if($plugininfo['website'])
			{
				$plugininfo['name'] = "<a href=\"".$plugininfo['website']."\">".$plugininfo['name']."</a>";
			}
			if($plugininfo['authorsite'])
			{
				$plugininfo['author'] = "<a href=\"".$plugininfo['authorsite']."\">".$plugininfo['author']."</a>";
			}
			if(isset($active_plugins[$codename]))
			{
				$pluginbuttons = "<input type=\"submit\" name=\"deactivate\" value=\"".$lang->deactivate_plugin."\" />";
			}
			else
			{
				$pluginbuttons = "<input type=\"submit\" name=\"activate\" value=\"".$lang->activate_plugin."\" />";
			}

			$bgcolor = getaltbg();
			startform("plugins.php");
			makehiddencode("plugin", $codename);
			makehiddencode("action", "activate");
			echo "<tr>\n";
			echo "<td class=\"$bgcolor\" width=\"50%\"><strong>".$plugininfo['name']."</strong><br /><small>".$plugininfo['description']."</small></td>\n";
			echo "<td class=\"$bgcolor\" width=\"10%\" align=\"center\">".$plugininfo['version']."</td>\n";
			echo "<td class=\"$bgcolor\" width=\"20%\" align=\"center\">".$plugininfo['author']."</td>\n";
			echo "<td class=\"$bgcolor\" width=\"20%\" align=\"center\">".$pluginbuttons."</td>\n";
			echo "</tr>\n";
			endform();
		}
	}
	else
	{
		echo "<tr>\n";
		echo "<td class=\"$bgcolor\" colspan=\"4\">".$lang->no_plugins."</td>\n";
		echo "</tr>\n";
	}
	endtable();
	cpfooter();
}
?>