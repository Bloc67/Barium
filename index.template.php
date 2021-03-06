<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package Bloc67	
 * @author bloc67 https://github.com/bloc67/Barium
 * @copyright 2022 Bloc67
 *
 * @version 2.1.0
 */

/**
 * Initialize the template... mainly little settings.
 */
function template_init()
{
	global $settings, $txt;

	$settings['theme_version'] = '2.1';
	$settings['require_theme_strings'] = true;
	$settings['avatars_on_indexes'] = true;
	$settings['avatars_on_boardIndex'] = true;
	$settings['login_main_menu'] = true;

	// This defines the formatting for the page indexes used throughout the forum.
	$settings['page_index'] = array(
		'extra_before' => '<span class="pages">' . $txt['pages'] . '</span>',
		'previous_page' => '<span class="main_icons previous_page"></span>',
		'current_page' => '<span class="current_page">%1$d</span> ',
		'page' => '<a class="nav_page" href="{URL}">%2$s</a> ',
		'expand_pages' => '<span class="expand_pages" onclick="expandPages(this, {LINK}, {FIRST_PAGE}, {LAST_PAGE}, {PER_PAGE});"> ... </span>',
		'next_page' => '<span class="main_icons next_page"></span>',
		'extra_after' => '',
	);

	// Allow css/js files to be disabled for this specific theme.
	// Add the identifier as an array key. IE array('smf_script'); Some external files might not add identifiers, on those cases SMF uses its filename as reference.
	if (!isset($settings['disable_files']))
		$settings['disable_files'] = array();

	loadtemplate('Barium');
}

/**
 * The main sub template above the content.
 */
function template_html_above()
{
	global $context, $scripturl, $txt, $modSettings, $settings;

	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', !empty($txt['lang_locale']) ? ' lang="' . str_replace("_", "-", substr($txt['lang_locale'], 0, strcspn($txt['lang_locale'], "."))) . '"' : '', '>
<head>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
	
	<meta charset="', $context['character_set'], '">';

	loadCSSFile('barium.css', array('minimize' => false));
	template_css();
	
	template_javascript();
	echo '
	<script src="' , $settings['theme_url'] , '/scripts/jquery-scrolltofixed-min.js" type="text/javascript"></script>
	<title>', $context['page_title_html_safe'], '</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">';
	foreach ($context['meta_tags'] as $meta_tag)
	{
		echo '
	<meta';
		foreach ($meta_tag as $meta_key => $meta_value)
			echo ' ', $meta_key, '="', $meta_value, '"';

		echo '>';
	}

	/*	What is your Lollipop's color?
		Theme Authors, you can change the color here to make sure your theme's main color gets visible on tab */
	echo '
	<meta name="theme-color" content="#557EA0">';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex">';

	// Present a canonical url for search engines to prevent duplicate content in their indices.
	if (!empty($context['canonical_url']))
		echo '
	<link rel="canonical" href="', $context['canonical_url'], '">';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help">
	<link rel="contents" href="', $scripturl, '">', ($context['allow_search'] ? '
	<link rel="search" href="' . $scripturl . '?action=search">' : '');

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['rss'], '" href="', $scripturl, '?action=.xml;type=rss2', !empty($context['current_board']) ? ';board=' . $context['current_board'] : '', '">
	<link rel="alternate" type="application/atom+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['atom'], '" href="', $scripturl, '?action=.xml;type=atom', !empty($context['current_board']) ? ';board=' . $context['current_board'] : '', '">';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['links']['next']))
		echo '
	<link rel="next" href="', $context['links']['next'], '">';

	if (!empty($context['links']['prev']))
		echo '
	<link rel="prev" href="', $context['links']['prev'], '">';

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0">';

	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'];

	echo '
</head>
<body id="', $context['browser_body_id'], '" class="action_', !empty($context['current_action']) ? $context['current_action'] : (!empty($context['current_board']) ?
		'messageindex' : (!empty($context['current_topic']) ? 'display' : 'home')), !empty($context['current_board']) ? ' board_' . $context['current_board'] : '', '">
	<section id="b_section_site">';
}

/**
 * The upper part of the main template layer. This is the stuff that shows above the main forum content.
 */
function template_body_above()
{
	global $context, $settings, $scripturl, $txt, $modSettings, $maintenance;

	// Wrapper div now echoes permanently for better layout options. h1 a is now target for "Go up" links.
	echo '
		<header id="b_header_site">
			<h1><a id="top" href="', $scripturl, '">', empty($context['header_logo_url_html_safe']) ? $context['forum_name_html_safe'] : '<img src="' . $context['header_logo_url_html_safe'] . '" alt="' . $context['forum_name_html_safe'] . '">', '</a></h1>
			<div id="b_slogan">', empty($settings['site_slogan']) ? '' : $settings['site_slogan'], '
			<nav id="b_nav_site">
				' , template_menu() , '
			</nav>
			<div id="b_user_site">';

			// If the user is logged in, display some things that might be useful.
			if ($context['user']['is_logged'])
			{
				// Firstly, the user's menu
				echo '
				<ul id="top_info">
					<li>
						<a href="', $scripturl, '?action=profile"', !empty($context['self_profile']) ? ' class="active"' : '', ' id="profile_menu_top" onclick="return false;">
							<b>', $context['user']['name'], '</b>
						</a>
						<ul id="profile_menu" class="top_menu"></ul>
					</li>';
		
				// Secondly, PMs if we're doing them
				if ($context['allow_pm'])
					echo '
					<li>
						<a href="', $scripturl, '?action=pm"', !empty($context['self_pm']) ? ' class="active"' : '', ' id="pm_menu_top">
							', $txt['pm_short'], ' ', !empty($context['user']['unread_messages']) ? '
							<span class="amt">' . $context['user']['unread_messages'] . '</span>' : '', '
						</a>
						<div id="pm_menu" class="top_menu"></div>
					</li>';
		
				// Thirdly, alerts
				echo '
					<li>
						<a href="', $scripturl, '?action=profile;area=showalerts;u=', $context['user']['id'], '"', !empty($context['self_alerts']) ? ' class="active"' : '', ' id="alerts_menu_top">
							', $txt['alerts'], ' ', !empty($context['user']['alerts']) ? '
							<span class="amt">' . $context['user']['alerts'] . '</span>' : '', '
						</a>
						<div id="alerts_menu" class="top_menu"></div>
					</li>';
		
				// A logout button for people without JavaScript.
				if (empty($settings['login_main_menu']))
					echo '
					<li id="nojs_logout">
						<a href="', $scripturl, '?action=logout;', $context['session_var'], '=', $context['session_id'], '">', $txt['logout'], '</a>
						<script>document.getElementById("nojs_logout").style.display = "none";</script>
					</li>';
		
				// And now we're done.
				echo '
				</ul>';
			}
			// Otherwise they're a guest. Ask them to either register or login.
			elseif (empty($maintenance))
			{
				// Some people like to do things the old-fashioned way.
				if (!empty($settings['login_main_menu']))
				{
					echo '
				<ul>
					<li class="welcome">', sprintf($txt[$context['can_register'] ? 'welcome_guest_register' : 'welcome_guest'], $context['forum_name_html_safe'], $scripturl . '?action=login', 'return reqOverlayDiv(this.href, ' . JavaScriptEscape($txt['login']) . ', \'login\');', $scripturl . '?action=signup'), '</li>
				</ul>';
				}
				else
				{
					echo '
				<ul id="top_info">
					<li class="welcome">
						', sprintf($txt['welcome_to_forum'], $context['forum_name_html_safe']), '
					</li>
					<li class="button_login">
						<a href="', $scripturl, '?action=login" class="', $context['current_action'] == 'login' ? 'active' : 'open','" onclick="return reqOverlayDiv(this.href, ' . JavaScriptEscape($txt['login']) . ', \'login\');">
							<span class="main_icons login"></span>
							<span class="textmenu">', $txt['login'], '</span>
						</a>
					</li>
					<li class="button_signup">
						<a href="', $scripturl, '?action=signup" class="', $context['current_action'] == 'signup' ? 'active' : 'open','">
							<span class="main_icons regcenter"></span>
							<span class="textmenu">', $txt['register'], '</span>
						</a>
					</li>
				</ul>';
				}
			}
			else
				// In maintenance mode, only login is allowed and don't show OverlayDiv
				echo '
				<ul class="welcome">
					<li>', sprintf($txt['welcome_guest'], $context['forum_name_html_safe'], $scripturl . '?action=login', 'return true;'), '</li>
				</ul>';
		
			if (!empty($modSettings['userLanguage']) && !empty($context['languages']) && count($context['languages']) > 1)
			{
				echo '
				<form id="languages_form" method="get">
					<select id="language_select" name="language" onchange="this.form.submit()">';
		
				foreach ($context['languages'] as $language)
					echo '
						<option value="', $language['filename'], '"', isset($context['user']['language']) && $context['user']['language'] == $language['filename'] ? ' selected="selected"' : '', '>', str_replace('-utf8', '', $language['name']), '</option>';
		
				echo '
					</select>
					<noscript>
						<input type="submit" value="', $txt['quick_mod_go'], '">
					</noscript>
				</form>';
			}
		
			if ($context['allow_search'])
			{
				echo '
				<form id="search_form" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
					<input type="search" name="search" value="">&nbsp;';
		
				// Using the quick search dropdown?
				$selected = !empty($context['current_topic']) ? 'current_topic' : (!empty($context['current_board']) ? 'current_board' : 'all');
		
				echo '
					<select name="search_selection">
						<option value="all"', ($selected == 'all' ? ' selected' : ''), '>', $txt['search_entireforum'], ' </option>';
		
				// Can't limit it to a specific topic if we are not in one
				if (!empty($context['current_topic']))
					echo '
						<option value="topic"', ($selected == 'current_topic' ? ' selected' : ''), '>', $txt['search_thistopic'], '</option>';
		
				// Can't limit it to a specific board if we are not in one
				if (!empty($context['current_board']))
					echo '
						<option value="board"', ($selected == 'current_board' ? ' selected' : ''), '>', $txt['search_thisboard'], '</option>';
		
				// Can't search for members if we can't see the memberlist
				if (!empty($context['allow_memberlist']))
					echo '
						<option value="members"', ($selected == 'members' ? ' selected' : ''), '>', $txt['search_members'], ' </option>';
		
				echo '
					</select>';
		
				// Search within current topic?
				if (!empty($context['current_topic']))
					echo '
					<input type="hidden" name="sd_topic" value="', $context['current_topic'], '">';
		
				// If we're on a certain board, limit it to this board ;).
				elseif (!empty($context['current_board']))
					echo '
					<input type="hidden" name="sd_brd" value="', $context['current_board'], '">';
		
				echo '
					<input type="submit" name="search2" value="', $txt['search'], '" class="button">
					<input type="hidden" name="advanced" value="0">
				</form>';
			}
			echo '					
			</div>
		</header>
		<aside id="b_aside_site">';

	if (!empty($settings['enable_news']) && !empty($context['random_news_line'])) {
		echo '
			<div class="b_aside_item">
				<h2>', $txt['news'], '</h2>
				<p>', $context['random_news_line'], '</p>
			</div>';
	}
	// any buttons we want on the side?
	if(function_exists('aside_buttons')) {
		aside_buttons();
	}

	echo '
		</aside>
		<article id="b_article">
			<nav id="b_nav_linktree">
				', theme_linktree() , '
			</nav>
			<main id="b_main_site">';
}

/**
 * The stuff shown immediately below the main content, including the footer
 */
function template_body_below()
{
	global $context, $txt, $scripturl, $modSettings, $settings, $options;

	echo '
			</main>
		</article>
		<footer id="b_footer_site">
			<ul>
				<li>', theme_copyright(), '
				<li><a href=""><b>Barium</b> theme by Bloc67</a></li>
				<li><a href="', $scripturl, '?action=help">', $txt['help'], '</a></li>
				', (!empty($modSettings['requireAgreement'])) ? '
				<li><a href="' . $scripturl . '?action=agreement">' . $txt['terms_and_rules'] . '</a></li>' : '', 
				'<li><a href="#b_header_site"><span class="b_up" title="', $txt['go_up'], '"></span></a></li>';

			// Show the load time?
	if ($context['show_load_time'])
		echo '
				<li>', sprintf($txt['page_created_full'], $context['load_time'], $context['load_queries']),'</li>';

	echo '			
			</ul>
		</footer>';
}

/**
 * This shows any deferred JavaScript and closes out the HTML
 */
function template_html_below()
{
	// Load in any javascipt that could be deferred to the end of the page
	template_javascript(true);

	echo '
	</section>
</body>
</html>';
}

/**
 * Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
 *
 * @param bool $force_show Whether to force showing it even if settings say otherwise
 */
function theme_linktree($force_show = false)
{
	global $context, $shown_linktree, $scripturl, $txt;

	// If linktree is empty, just return - also allow an override.
	if (empty($context['linktree']) || (!empty($context['dont_default_linktree']) && !$force_show))
		return;

	echo '
				<ul>';

	// Each tree item has a URL and name. Some may have extra_before and extra_after.
	foreach ($context['linktree'] as $link_num => $tree)
	{
		echo '
					<li>';

		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'], ' ';

		// Show the link, including a URL if it should have one.
		if (isset($tree['url']))
			echo '
						<a href="' . $tree['url'] . '"><span>' . $tree['name'] . '</span></a>';
		else
			echo '
						<span>' . $tree['name'] . '</span>';

		// Show something after the link...?
		if (isset($tree['extra_after']))
			echo ' ', $tree['extra_after'];

		echo '
					</li>';
	}

	echo '
				</ul>';

	$shown_linktree = true;
}

/**
 * Show the menu up top. Something like [home] [help] [profile] [logout]...
 */
function template_menu()
{
	global $context;

	echo '
				<ul>';

	// Note: Menu markup has been cleaned up to remove unnecessary spans and classes.
	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
					<li class="button_', $act, '', !empty($button['sub_buttons']) ? ' subsections"' : '"', '>
						<a', $button['active_button'] ? ' class="active"' : '', ' href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', isset($button['onclick']) ? ' onclick="' . $button['onclick'] . '"' : '', '>
							', $button['title'], !empty($button['amt']) ? ' <span class="amt">' . $button['amt'] . '</span>' : '', '
						</a>';

		// 2nd level menus
		if (!empty($button['sub_buttons']))
		{
			echo '
						<ul>';

			foreach ($button['sub_buttons'] as $childbutton)
			{
				echo '
							<li', !empty($childbutton['sub_buttons']) ? ' class="subsections"' : '', '>
								<a href="', $childbutton['href'], '"', isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', isset($childbutton['onclick']) ? ' onclick="' . $childbutton['onclick'] . '"' : '', '>
									', $childbutton['title'], !empty($childbutton['amt']) ? ' <span class="amt">' . $childbutton['amt'] . '</span>' : '', '
								</a>';
				// 3rd level menus :)
				if (!empty($childbutton['sub_buttons']))
				{
					echo '
								<ul>';

					foreach ($childbutton['sub_buttons'] as $grandchildbutton)
						echo '
									<li>
										<a href="', $grandchildbutton['href'], '"', isset($grandchildbutton['target']) ? ' target="' . $grandchildbutton['target'] . '"' : '', isset($grandchildbutton['onclick']) ? ' onclick="' . $grandchildbutton['onclick'] . '"' : '', '>
											', $grandchildbutton['title'], !empty($grandchildbutton['amt']) ? ' <span class="amt">' . $grandchildbutton['amt'] . '</span>' : '', '
										</a>
									</li>';

					echo '
								</ul>';
				}

				echo '
							</li>';
			}
			echo '
						</ul>';
		}
		echo '
					</li>';
	}

	echo '
				</ul>';
}

/**
 * Generate a strip of buttons.
 *
 * @param array $button_strip An array with info for displaying the strip
 * @param string $direction The direction
 * @param array $strip_options Options for the button strip
 */
function template_button_strip($button_strip, $direction = '', $strip_options = array())
{
	global $context, $txt;

	if (!is_array($strip_options))
		$strip_options = array();

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		// As of 2.1, the 'test' for each button happens while the array is being generated. The extra 'test' check here is deprecated but kept for backward compatibility (update your mods, folks!)
		if (!isset($value['test']) || !empty($context[$value['test']]))
		{
			if (!isset($value['id']))
				$value['id'] = $key;

			$button = '
				<a class="button button_strip_' . $key . (!empty($value['active']) ? ' active' : '') . (isset($value['class']) ? ' ' . $value['class'] : '') . '" ' . (!empty($value['url']) ? 'href="' . $value['url'] . '"' : '') . ' ' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '>'.(!empty($value['icon']) ? '<span class="main_icons '.$value['icon'].'"></span>' : '').'' . $txt[$value['text']] . '</a>';

			if (!empty($value['sub_buttons']))
			{
				$button .= '
					<div class="top_menu dropmenu ' . $key . '_dropdown">
						<div class="viewport">
							<div class="overview">';
				foreach ($value['sub_buttons'] as $element)
				{
					if (isset($element['test']) && empty($context[$element['test']]))
						continue;

					$button .= '
								<a href="' . $element['url'] . '"><strong>' . $txt[$element['text']] . '</strong>';
					if (isset($txt[$element['text'] . '_desc']))
						$button .= '<br><span>' . $txt[$element['text'] . '_desc'] . '</span>';
					$button .= '</a>';
				}
				$button .= '
							</div><!-- .overview -->
						</div><!-- .viewport -->
					</div><!-- .top_menu -->';
			}

			$buttons[] = $button;
		}
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	echo '
		<div class="buttonlist', !empty($direction) ? ' float' . $direction : '', '"', (empty($buttons) ? ' style="display: none;"' : ''), (!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"' : ''), '>
			', implode('', $buttons), '
		</div>';
}


// the theme button rendering, keeping the original to avoid crashes between admin sections and main sections
function b_button_strip($button_strip, $direction = '', $strip_options = array())
{
	global $context, $txt;

	if (!is_array($strip_options))
		$strip_options = array();

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		// As of 2.1, the 'test' for each button happens while the array is being generated. The extra 'test' check here is deprecated but kept for backward compatibility (update your mods, folks!)
		if (!isset($value['test']) || !empty($context[$value['test']]))
		{
			if (!isset($value['id']))
				$value['id'] = $key;

			$button = '
				<li><a class="b_strip_' . $key . (!empty($value['active']) ? ' active' : '') . (isset($value['class']) ? ' ' . $value['class'] : '') . '" ' . (!empty($value['url']) ? 'href="' . $value['url'] . '"' : '') . ' ' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '>'.(!empty($value['icon']) ? '<span class="main_icons '.$value['icon'].'"></span>' : '').'' . $txt[$value['text']] . '</a>';

			if (!empty($value['sub_buttons']))
			{
				$button .= '
					<ul>';
				foreach ($value['sub_buttons'] as $element)
				{
					if (isset($element['test']) && empty($context[$element['test']]))
						continue;

					$button .= '
						<li><a href="' . $element['url'] . '">' . $txt[$element['text']];
					if (isset($txt[$element['text'] . '_desc']))
						$button .= '<span>' . $txt[$element['text'] . '_desc'] . '</span>';
					$button .= '</a>
						</li>';
				}
				$button .= '
					</ul>';
			}
			$button .= '
				</li>';
			$buttons[] = $button;
		}
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	echo '
		<ul class="b_buttonlist"', (empty($buttons) ? ' style="display: none;"' : ''), (!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"' : ''), '>
			', implode('', $buttons), '
		</ul>';
}

/**
 * Generate a list of quickbuttons.
 *
 * @param array $list_items An array with info for displaying the strip
 * @param string $list_class Used for integration hooks and as a class name
 * @param string $output_method The output method. If 'echo', simply displays the buttons, otherwise returns the HTML for them
 * @return void|string Returns nothing unless output_method is something other than 'echo'
 */
function template_quickbuttons($list_items, $list_class = null, $output_method = 'echo')
{
	global $txt;

	// Enable manipulation with hooks
	if (!empty($list_class))
		call_integration_hook('integrate_' . $list_class . '_quickbuttons', array(&$list_items));

	// Make sure the list has at least one shown item
	foreach ($list_items as $key => $li)
	{
		// Is there a sublist, and does it have any shown items
		if ($key == 'more')
		{
			foreach ($li as $subkey => $subli)
				if (isset($subli['show']) && !$subli['show'])
					unset($list_items[$key][$subkey]);

			if (empty($list_items[$key]))
				unset($list_items[$key]);
		}
		// A normal list item
		elseif (isset($li['show']) && !$li['show'])
			unset($list_items[$key]);
	}

	// Now check if there are any items left
	if (empty($list_items))
		return;

	// Print the quickbuttons
	$output = '
		<ul class="quickbuttons' . (!empty($list_class) ? ' quickbuttons_' . $list_class : '') . '">';

	// This is used for a list item or a sublist item
	$list_item_format = function($li)
	{
		$html = '
			<li' . (!empty($li['class']) ? ' class="' . $li['class'] . '"' : '') . (!empty($li['id']) ? ' id="' . $li['id'] . '"' : '') . (!empty($li['custom']) ? ' ' . $li['custom'] : '') . '>';

		if (isset($li['content']))
			$html .= $li['content'];
		else
			$html .= '
				<a href="' . (!empty($li['href']) ? $li['href'] : 'javascript:void(0);') . '"' . (!empty($li['javascript']) ? ' ' . $li['javascript'] : '') . '>
					' . (!empty($li['icon']) ? '<span class="main_icons ' . $li['icon'] . '"></span>' : '') . (!empty($li['label']) ? $li['label'] : '') . '
				</a>';

		$html .= '
			</li>';

		return $html;
	};

	foreach ($list_items as $key => $li)
	{
		// Handle the sublist
		if ($key == 'more')
		{
			$output .= '
			<li class="post_options">
				<a href="javascript:void(0);">' . $txt['post_options'] . '</a>
				<ul>';

			foreach ($li as $subli)
				$output .= $list_item_format($subli);

			$output .= '
				</ul>
			</li>';
		}
		// Ordinary list item
		else
			$output .= $list_item_format($li);
	}

	$output .= '
		</ul><!-- .quickbuttons -->';

	// There are a few spots where the result needs to be returned
	if ($output_method == 'echo')
		echo $output;
	else
		return $output;
}

/**
 * The upper part of the maintenance warning box
 */
function template_maint_warning_above()
{
	global $txt, $context, $scripturl;

	echo '
	<div class="errorbox" id="errors">
		<dl>
			<dt>
				<strong id="error_serious">', $txt['forum_in_maintenance'], '</strong>
			</dt>
			<dd class="error" id="error_list">
				', sprintf($txt['maintenance_page'], $scripturl . '?action=admin;area=serversettings;' . $context['session_var'] . '=' . $context['session_id']), '
			</dd>
		</dl>
	</div>';
}

/**
 * The lower part of the maintenance warning box.
 */
function template_maint_warning_below()
{

}

?>