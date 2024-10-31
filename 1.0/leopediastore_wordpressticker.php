<?php
/*
  Plugin Name: Leopedia Store Ticker Plugin
  Plugin URI: http://leopediastore.com
  Description: Leopedia Store News Ticker Plugin
  Version: 1.0
  Author: Leopedia Web solutions
  Author URI: http://leopediastore.com
 */

// include
include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . "wp-config.php");
include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . "wp-includes/wp-db.php");

/**
 * Get news ticker settings
 * @return: array $newsTickerOpts
 */
function getNewsTickerOptions() {
    // get setting
    $newsTickerOpts = get_option("newsTickerOpts");

    // default settings
    $defaultOpts = array(
        "category" => 0, // default: all categories
        "noOfPosts" => 10, // total posts
        "isTodayPosts" => 1, // today posts
        "boxBackground" => "#FFFFFF", // box background color
        "boxWidth" => 800, // box width (px)
        "boxHeight" => 50, // box height (px)
        "postColor" => "#000000", // font color
        "postSize" => 12, // font size (px)
        "titleText" => "NEW: ",
        "titleSize" => 12,
        "titleColor" => "#8B0000",
        "dateColor" => "#404040",
        "dateSize" => 11
    );

    return wp_parse_args((array) $newsTickerOpts, $defaultOpts);
}

/**
 * Update settings
 * @param array $newOpts: new settings
 * @param array $curOpts: current settings
 * @return array $opts: merged settings
 */
function updateNewsTickerOptions($newOpts, $curOpts) {
    $opts = wp_parse_args((array) $newOpts, $curOpts);
    update_option("newsTickerOpts", $opts);
    return $opts;
}

/**
 *  News ticker admin page
 */
function adminNewsTicker() {
    global $wpdb;

    // get settings
    $curOpts = getNewsTickerOptions();

    // get categories
    $categories = get_categories();

    // submited flag
    $isSubmit = false;

    // updated flag
    $isUpdated = false;

    if (isset($_POST['submit']) && !empty($_POST['submit'])) {
        $isSubmit = true;
    }

    // submit
    if ($isSubmit) {
        // integer fields
        $intFields = array(
            "category",
            "noOfPosts",
            "isTodayPosts",
            "boxWidth",
            "boxHeight",
            "postSize",
            "titleSize",
            "dateSize"
        );

        // get new settings		
        foreach ($curOpts as $k => $v) {
            if (isset($_POST[$k]) && $_POST[$k] != '') {
                // intval
                if (in_array($k, $intFields)) {
                    if (is_numeric($_POST[$k])) {
                        $newOpts[$k] = intval($_POST[$k]);
                    }
                } else {
                    if (get_magic_quotes_gpc()) {
                        $value = stripslashes($_POST[$k]);
                    }
                    $newOpts[$k] = $wpdb->escape($value);
                }
            }
        }

        // today posts
        if (
                !isset($newOpts["isTodayPosts"])
                || ($newOpts["isTodayPosts"] != "1" && $newOpts["isTodayPosts"] != 1)
        ) {
            $newOpts["isTodayPosts"] = 0;
        }

        // update settings
        $curOpts = updateNewsTickerOptions($newOpts, $curOpts);

        // updated flag
        $isUpdated = true;
    }

    // html
    $html = '
		<div class="wrap">
		<h2>News Ticker </h2> <h4>by <a href="http://leopediastore.com">LeoPedia Web Solutions </a></h4>
	';

    // updated
    if ($isUpdated) {
        $html .= '
			<div class="updated">Your changes were updated!</div>
		';
    }

    // form action
    $formAction = str_replace('%7E', '~', $_SERVER['REQUEST_URI']);

    // category
    $html .= '
		<div id="poststuff">
			<form id="newsTickerFrm" action="' . $formAction . '" method="post" onsubmit="return onNewsTicketFrmSubmit();">
			<input type="hidden" name="submit" value="1" />
			<div class="stuffbox">
				<h3>Category</h3>
				<div class="inside">
					<select id="newsTickerCategory" name="category">
					<option value="0">All categories</option>';
    foreach ($categories as $category) {
        $html .= '
					<option value="' . $category->term_id . '">' . $category->name . '</option>
		';
    }

    $html .= '
					</select>
				</div>
				<script type="text/javascript">
					document.getElementById("newsTickerCategory").value = "' . $curOpts['category'] . '";
					document.getElementById("newsTickerCategory").defaultValue = "' . $curOpts['category'] . '";
				</script>
			</div>
	';

    /**
     * today posts, total posts
     */
    $html .= '
			<div class="stuffbox">
				<h3>Posts</h3>
				<div class="inside">
					<select id="newsTickerTodayPosts" name="isTodayPosts" onchange="onTodayPostsFieldChange();">
			';

    // today posts
    if ($curOpts['isTodayPosts'] == 1) {
        $html .= '
					<option value="1" selected>Today</option>
					<option value="0">Total posts</option>
		';
    } else {
        $html .= '
					<option value="1">Today</option>
					<option value="0" selected>Total posts</option>
		';
    }
    $html .= '
					</select>
				';

    // total posts
    $html .= '
					<input type="text" value="' . $curOpts['noOfPosts'] . '" id="newsTickerNoOfPosts" name="noOfPosts" style="width: 40px;" />
				</div>
	';

    $html .= '
				<script type="text/javascript">
				function onTodayPostsFieldChange() {
					var todayPostsField = document.getElementById("newsTickerTodayPosts");
					var totalPostsField = document.getElementById("newsTickerNoOfPosts");
					if (todayPostsField.value == "0" || todayPostsField.value == 0) {
						totalPostsField.style.display = "";
					}
					else {
						totalPostsField.style.display = "none";
					}
				}
				onTodayPostsFieldChange();
				</script>
			</div>
	';

    /**
     * Box styles
     */
    $html .= '
			<div id="namediv" class="stuffbox">
				<h3>Box styles</h3>
				<div class="inside">
					<table class="form-table editcomment">';

    // box background
    $html .= '
					<tr valign="top">
						<td class="first"><label for="newsTickerBoxBackground">Background color</label></td>
						<td><input type="text" name="boxBackground" id="newsTickerBoxBackground" value="' . $curOpts['boxBackground'] . '" style="width: 100px;" /> (#FFFFFF)</td>
					</tr>';

    // box width
    $html .= '
					<tr valign="top">
						<td class="first"><label for="newsTickerBoxWidth">Width</label></td>
						<td><input type="text" name="boxWidth" id="newsTickerBoxWidth" value="' . $curOpts['boxWidth'] . '" style="width: 100px;" /> (px)</td>
					</tr>';

    // box height
    $html .= '
					<tr valign="top">
						<td class="first"><label for="newsTickerBoxHeight">Height</label></td>
						<td><input type="text" name="boxHeight" id="newsTickerBoxHeight" value="' . $curOpts['boxHeight'] . '" style="width: 100px;" /> (px)</td>
					</tr>';

    $html .= '
					</table>
				</div>
			</div>
	';

    /**
     * Post styles
     */
    $html .= '
			<div id="namediv" class="stuffbox">
				<h3>Post styles</h3>
				<div class="inside">
					<table class="form-table editcomment">';

    // post color
    $html .= '
					<tr valign="top">
						<td class="first"><label for="newsTickerPostColor">Font color</label></td>
						<td><input type="text" name="postColor" id="newsTickerPostColor" value="' . $curOpts['postColor'] . '" style="width: 100px;" /> (#000000)</td>
					</tr>';

    // post size
    $html .= '
					<tr valign="top">
						<td class="first"><label for="newsTickerPostSize">Font size</label></td>
						<td><input type="text" name="postSize" id="newsTickerPostSize" value="' . $curOpts['postSize'] . '" style="width: 100px;" /> (px)</td>
					</tr>';
    $html .= '
					</table>
				</div>
			</div>
	';

    /**
     * Title styles
     */
    $html .= '
			<div id="namediv" class="stuffbox">
				<h3>Title styles</h3>
				<div class="inside">
					<table class="form-table editcomment">';
    // title text
    $html .= '
					<tr valign="top">
						<td class="first"><label for="newsTickerTitleText">Text</label></td>
						<td><input type="text" name="titleText" id="newsTickerTitleText" value="' . str_replace('\\', '', $curOpts['titleText']) . '" style="width: 100px;" /> ("New: ")</td>
					</tr>';

    // title color
    $html .= '
					<tr valign="top">
						<td class="first"><label for="newsTickerTitleColor">Font color</label></td>
						<td><input type="text" name="titleColor" id="newsTickerTitleColor" value="' . $curOpts['titleColor'] . '" style="width: 100px;" /> (#000000)</td>
					</tr>';

    // title size
    $html .= '
					<tr valign="top">
						<td class="first"><label for="newsTickerTitleSize">Font size</label></td>
						<td><input type="text" name="titleSize" id="newsTickerTitleSize" value="' . $curOpts['titleSize'] . '" style="width: 100px;" /> (px)</td>
					</tr>';
    $html .= '
					</table>
				</div>
			</div>
	';

    /**
     * Date styles
     */
    $html .= '
			<div id="namediv" class="stuffbox">
				<h3>Date styles</h3>
				<div class="inside">
					<table class="form-table editcomment">';
    // date color
    $html .= '
					<tr valign="top">
						<td class="first"><label for="newsTickerDateColor">Font color</label></td>
						<td><input type="text" name="dateColor" id="newsTickerDateColor" value="' . $curOpts['dateColor'] . '" style="width: 100px;" /> (#000000)</td>
					</tr>';

    // title size
    $html .= '
					<tr valign="top">
						<td class="first"><label for="newsTickerDateSize">Font size</label></td>
						<td><input type="text" name="dateSize" id="newsTickerDateSize" value="' . $curOpts['dateSize'] . '" style="width: 100px;" /> (px)</td>
					</tr>';
    $html .= '
					</table>
				</div>
			</div>
	';

    // submit button
    $html .= '
			<div>
				<input type="submit" value="Save changes" class="button-primary" />
			</div>
	';

    // note
    $html .= '
			<br />
			<div class="description">
			Add <b>&lt;?php showTicker(); ?&gt;</b> where you want to show
			</div>
	';


    $html .= '
			</form>
			</div>
		</div>
		<script type="text/javascript">
		function getEl(id) {
			return document.getElementById(id);
		}
		function onNewsTicketFrmSubmit() {
			if (getEl("newsTickerTodayPosts").value == "0") {
				if (!isInt(getEl("newsTickerNoOfPosts").value)) {
					alert("Please input a number that greater than zero");
					getEl("newsTickerNoOfPosts").focus();
					return false;
				}
			}
			if (!isInt(getEl("newsTickerBoxWidth").value)) {
				alert("Please input a number that greater than zero");
				getEl("newsTickerBoxWidth").focus();
				return false;
			}
			if (!isInt(getEl("newsTickerBoxHeight").value)) {
				alert("Please input a number that greater than zero");
				getEl("newsTickerBoxHeight").focus();
				return false;
			}
			if (!isInt(getEl("newsTickerPostSize").value)) {
				alert("Please input a number that greater than zero");
				getEl("newsTickerPostSize").focus();
				return false;
			}
			if (!isInt(getEl("newsTickerTitleSize").value)) {
				alert("Please input a number that greater than zero");
				getEl("newsTickerTitleSize").focus();
				return false;
			}
			if (!isInt(getEl("newsTickerDateSize").value)) {
				alert("Please input a number that greater than zero");
				getEl("newsTickerDateSize").focus();
				return false;
			}
			if (!isHexColor(getEl("newsTickerBoxBackground").value)) {
				alert("Please input a valid hex color");
				getEl("newsTickerBoxBackground").focus();
				return false;
			}
			if (!isHexColor(getEl("newsTickerPostColor").value)) {
				alert("Please input a valid hex color");
				getEl("newsTickerPostColor").focus();
				return false;
			}
			if (!isHexColor(getEl("newsTickerTitleColor").value)) {
				alert("Please input a valid hex color");
				getEl("newsTickerTitleColor").focus();
				return false;
			}
			if (!isHexColor(getEl("newsTickerDateColor").value)) {
				alert("Please input a valid hex color");
				getEl("newsTickerDateColor").focus();
				return false;
			}
			return true;
		}
		function isInt(value) {
			var testResult = /^\d+$/.test(value);
			if (!testResult) {
				return false;
			}
			value = parseInt(value);
			if (value <= 0) {
				return false;
			}
			return true;
		}
		function isHexColor(value) {
			var strPattern = /^#([0-9a-f]{1,2}){3}$/i;
			return strPattern.test(value);
		}
		</script>
	';
    echo $html;
}

/**
 * Get posts
 * @param array $opts: settings
 * @return array $posts
 */
function getPosts($opts) {
    // criterias
    $criterias = array();

    // category
    if ($opts['category'] != 0) {
        $criterias['category'] = $opts['category'];
    }

    // today
    if ($opts['isTodayPosts'] == 1) {
        $today = getdate();
        $criterias['year'] = $today["year"];
        $criterias['monthnum'] = $today["mon"];
        $criterias['day'] = $today["mday"];
    }
    // total posts
    else {
        $criterias['numberposts'] = $opts['noOfPosts'];
    }
    return get_posts($criterias);
}

/**
 * Show ticker
 */
function showTicker() {
    // get settings
    $opts = getNewsTickerOptions();

    // get posts

    $posts = getPosts($opts);
    // if empty posts
    if (empty($posts)) {
        return false;
    }

    $html = '
		<div id="ticker-wrapper" class="no-js">
			<ul id="newsTicker" class="js-hidden">';

    foreach ($posts as $post) {
        if ($opts['isTodayPosts'] == 1) {
            $postDate = strftime("%H:%M %p", strtotime($post->post_date));
        } else {
            $postDate = $post->post_date;
        }


        $link = $post->guid;
        $title = $post->post_title;
        $html .= '
				<li class="news-item">
					<span class="news-date">' . $postDate . ' >></span> <a href="' . $link . '" title="' . $title . '">' . $title . '</a>
				</li>
		';
    }

    $html .= '			
			</ul>
		</div>
	';
    echo $html;
}

/**
 * Add script, css
 */
function addHeadScript() {
    // get settings
    $opts = getNewsTickerOptions();

    // plugin directory
    $pluginDir = WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__));
    $top = intval(($opts['boxHeight'] + $opts['postSize']) / 2);
    $script = '
		<style type="text/css">		
		/* Ticker Styling */
		.ticker-wrapper *{margin:0}
		.ticker-wrapper.has-js {
			padding-left: 20px;
			width: ' . $opts['boxWidth'] . 'px;
			height: ' . $opts['boxHeight'] . 'px;
			display: block;
			background-color: ' . $opts['boxBackground'] . ';
			font-size: ' . $opts['postSize'] . 'px;
		}
		.ticker {
			width: ' . ($opts['boxWidth'] - 85) . 'px;
			height: ' . $opts['boxHeight'] . 'px;
			display: block;
			float: left;
			position: relative;
			overflow: hidden;
			background-color: ' . $opts['boxBackground'] . ';
		}
		.ticker-title {
			float: left; 	
			color: ' . $opts['titleColor'] . ';
			font-weight: bold;
			background-color: ' . $opts['boxBackground'] . ';
			text-transform: uppercase;
			font-size: ' . $opts['titleSize'] . 'px;
			height: ' . $opts['boxHeight'] . 'px;
			line-height: ' . $opts['boxHeight'] . 'px;
						
		}
		
		.ticker-content {
			margin: 0px;
			float: left;
			position: absolute;
			color: ' . $opts['postColor'] . ';
			font-weight: bold;
			background-color: ' . $opts['boxBackground'] . ';
			overflow: hidden;
			white-space: nowrap;
			font-size: ' . $opts['postSize'] . 'px;
			line-height: ' . $opts['boxHeight'] . 'px;
		}
		.ticker-content:focus {
			none;
		}
		.ticker-content a {
			text-decoration: none;
			font-size: ' . $opts['postSize'] . 'px;	
			color: ' . $opts['postColor'] . ';
		}
		.ticker-content a:hover {
			text-decoration: underline;				
			color: ' . $opts['postColor'] . ';
		}
		.ticker-swipe {
			position: absolute;
			top: 0px;
			left: 1200px;
			background-color: ' . $opts['boxBackground'] . ';
			display: block;
			width: ' . $opts['boxWidth'] . 'px;
			height: ' . $opts['boxHeight'] . 'px; 
		}
		.ticker-swipe span {
			margin-left: 1px;
			background-color: ' . $opts['boxBackground'] . ';
			border-bottom: 1px solid ' . $opts['postColor'] . ';
			height: ' . ($top - 1) . 'px;
			width: 7px;
			display: block;
		}
		.news-date {
			font-size: ' . $opts['dateSize'] . 'px;
			color: ' . $opts['dateColor'] . ';
			font-weight: normal;
		}
		.ticker-controls{list-style-type:none;float:right;padding:' . ($top - 16) . 'px 10px 0 0}
		.ticker-controls LI{margin-left:5px;float:left;cursor:pointer;height:16px;width:16px;display:block;padding:0}
		.ticker-controls LI#play-pause{background-image:url(' . $pluginDir . '/images/controls.png);background-position:32px 16px}
		.ticker-controls LI#play-pause.over{background-position:32px 32px}
		.ticker-controls LI#play-pause.down{background-position:32px 0}
		.ticker-controls LI#play-pause.paused{background-image:url(' . $pluginDir . '/images/controls.png);background-position:48px 16px}
		.ticker-controls LI#play-pause.paused.over{background-position:48px 32px}
		.ticker-controls LI#play-pause.paused.down{background-position:48px 0}
		.ticker-controls LI#prev{background-image:url(' . $pluginDir . '/images/controls.png);background-position:0 16px}
		.ticker-controls LI#prev.over{background-position:0 32px}
		.ticker-controls LI#prev.down{background-position:0 0}
		.ticker-controls LI#next{background-image:url(' . $pluginDir . '/images/controls.png);background-position:16px 16px}
		.ticker-controls LI#next.over{background-position:16px 32px}
		.ticker-controls LI#next.down{background-position:16px 0}
		.js-hidden{display:none}
		</style>
		<script type="text/javascript">
		jQuery.noConflict();		
		</script>
		<script type="text/javascript" src="' . $pluginDir . '/jquery.ticker.min.js"></script>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#newsTicker").ticker({
					titleText: "' . $opts['titleText'] . '"
				}
			);		
		});
		</script>
	';
    echo $script;
}

// add to admin menu
add_action('admin_menu', 'addNewsTickerMenu');

function addNewsTickerMenu() {
    add_submenu_page('plugins.php', 'News Ticker', 'News Ticker', 10, 'newsTickerAdmin', 'adminNewsTicker');
}

// add jquery
wp_enqueue_script('jquery');
// add script
add_action('wp_head', 'addHeadScript');
?>