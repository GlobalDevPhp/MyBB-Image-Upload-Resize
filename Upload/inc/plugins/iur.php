<?php

/*
Image Upload Resize Plugin for MyBB 1.8.x
Copyright (C) 2018 CoolMoon

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/


// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook("admin_config_settings_change","iur_settings_change");
$plugins->add_hook("admin_settings_print_peekers","iur_settings_peek");
$plugins->add_hook("upload_attachment_thumb_start","iur_resize");


function iur_info()
{
	return array(
		"name"			=> "Image Upload Resize",
		"description"	=> "Reduce the size of images uploaded to your forum",
		"website"		=> "http://modificationx.com",
		"author"		=> "CoolMoon",
		"authorsite"	=> "www.modificationx.com",
		"version"		=> "0.1",
		"compatibility" => "18*",
		"codename"		=>"IUR"
	);
}

function iur_install()
{
	
		global $db, $lang ;

	$lang->load("iur");
	
		$settings_group = array(
		"name" 			=> "Image_Upload_Resize",
		"title"			=> $lang->iur_title,
		"description" 	=> $lang->iur_descr,
		"disporder" 	=> "1",
		"isdefault" 	=> "no"
	);
	$db->insert_query('settinggroups',$settings_group);
	$gid = $db->insert_id();
	
		$setting = array(
		"name" 			=> "iur_width_size",
		"title" 		=> $lang->iur_size_width_title,
		"description" 	=> $lang->iur_size_width_descr,
		"optionscode" 	=> "numeric",
		"value" 		=> "1200",
		"disporder" 	=> "2",
		"gid" 			=> intval($gid)
	);
	$db->insert_query('settings',$setting);
	
		$setting = array(
		"name" 			=> "iur_height_size",
		"title" 		=> $lang->iur_size_height_title,
		"description" 	=> $lang->iur_size_height_descr,
		"optionscode" 	=> "numeric",
		"value" 		=> "1200",
		"disporder" 	=> "3",
		"gid" 			=> intval($gid)
	);
	$db->insert_query('settings',$setting);
		
		$setting = array(
		"name" 			=> 'iur_jpg_compression_setting',
		"title" 		=> $lang->iur_jpg_compression_setting_title,
		"description" 	=> $lang->iur_jpg_compression_setting_descr,
		"optionscode" 	=> 'yesno',
		"value" 		=> '0',
		"disporder" 	=> "4",
		"gid" 			=> intval($gid)
	);
	$db->insert_query('settings',$setting);
	
	
		$setting = array(
		"name" 			=> 'iur_quality_setting',
		"title" 		=> $lang->iur_quality_setting_title,
		"description" 	=> $lang->iur_quality_setting_descr,
		"optionscode" 	=> 'numeric',
		"value" 		=> '85',
		"disporder" 	=> "5",
		"gid" 			=> intval($gid)
	);
	$db->insert_query('settings',$setting);
	

	rebuild_settings();

}

function iur_is_installed()
{
    global $db;

	$query = $db->simple_select("settinggroups", "*", "name='Image_Upload_Resize'");
	$setting_group = $db->fetch_array($query);

    if($setting_group)
    {
        return true;
    }

    return false;
}

function iur_uninstall()
{
	global $db;

	$db->delete_query('settinggroups', "name = 'Image_Upload_Resize'");
	$db->delete_query('settings', "name IN ('iur_enable', 'iur_width_size', 'iur_height_size', 'iur_jpg_compression_setting', 'iur_quality_setting' )");


	rebuild_settings();
}

function iur_resize($attacharray)
{
	//ini_set('display_errors', '1');
	require_once MYBB_ROOT."inc/functions_image.php";
	global $mybb, $db;

	//hooks 'upload_attachment_thumb_start' to get image after upload and name change but before thumbnail is made.

	//return if $attacharray empty
	if(!isset($attacharray))
	{
		return $attacharray;
	}
	
	//get upload folder and .attach name from $attacharray
	$resize_path = explode('/', $attacharray[attachname]);
	
	//use MyBB generate_thumbnail to resize and overwrite .attach image 
	$upload = generate_thumbnail($mybb->settings['uploadspath']."/".$attacharray[attachname],$mybb->settings['uploadspath']."/".$resize_path[0],$resize_path[1], $mybb->settings['iur_width_size'], $mybb->settings['iur_height_size']);

	//get new file size and update $attacharray
	$size = filesize($mybb->settings['uploadspath']."/".$attacharray[attachname]);
	$attacharray[filesize] = $size;

	
return $attacharray;


	
	
}

function iur_settings_change()
{
	global $db,$mybb,$iur_settings_peeker;
	//$info=iur_info();
	$result=$db->simple_select("settinggroups","gid","name='Image_Upload_Resize'",array("limit"=>1));
	$group=$db->fetch_array($result);
	$iur_settings_peeker=($mybb->input["gid"]==$group["gid"])&&($mybb->request_method!="post");
	
}
function iur_settings_peek(&$peekers)
{
	global $db,$mybb, $iur_settings_peeker;
	 if ($iur_settings_peeker) 
		{
        // Peeker for author moderation settings
        $peekers[] = 'new Peeker($(".setting_iur_jpg_compression_setting"), $("#setting_iur_quality_setting"), /1/, true)';
		}
		 

}
?>