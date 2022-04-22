<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2022 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1.0
 */

/**
 * The top part of the outer layer of the boardindex
 */
function template_boardindex_outer_above() 
{
	global $txt, $options;

	echo '
	<section id="b_boardindex_tabs" class="b_section_tabs">
		<ul>
			<li><a id="b_bi_1tab" data-item="b_bi_tab1_section" class="b_tabs active">' , $txt['b_bi_tab1'] , '</a></li>
			<li><a id="b_bi_2tab"  data-item="b_bi_tab2_section" class="b_tabs">' , $txt['b_bi_tab2'] , '</a></li>
		</ul>
		<main>
			<div id="b_bi_tab1_section" class="visible">
				' , template_tab_bcats() , '
				' , template_tab_boardlist() , '
				' , template_tab_bdetail() , '
			</div>
			<div id="b_bi_tab2_section">
				' , template_tab_ic() , '
				' , template_tab_ic_selected() , '
			</div>
		</main>
	</section>			
			';
}

/**
 * This shows the newsfader
 */
function template_newsfader()
{
	global $context, $settings, $txt;

	// Show the news fader?  (assuming there are things to show...)
	if (!empty($settings['show_newsfader']) && !empty($context['news_lines']))
	{
		echo '
	<section id="b_newsfader">
		<ul id="smf_slider">';

		foreach ($context['news_lines'] as $news)
			echo '
			<li>', $news, '</li>';

		echo '
		</ul>
	</section>	
		<script>
			jQuery("#smf_slider").slippry({
				pause: ', $settings['newsfader_time'], ',
				adaptiveHeight: 0,
				captions: 0,
				controls: 0,
			});
		</script>';
	}
}

/**
 * This actually displays the board index
 */
function template_main()
{
	return;
}

/* show only the categories, with jumpers to each */
function template_tab_bcats()
{
	global $context, $txt, $scripturl;

	echo '
<div>
	<dl id="b_bi_cats">';

	foreach ($context['categories'] as $category)
	{
		// If theres no parent boards we can see, avoid showing an empty category (unless its collapsed)
		if (empty($category['boards']) && !$category['is_collapsed'])
			continue;

		echo '
		<dt>
			<a href="#b_cat_' , $category['id'] , '">', $category['name'], '</a>
			<ul class="b_sublinks">
				<li><a href="' , $category['href'] , '">' , $txt['unread'] , '</a></li>';

		echo '
			</ul>
		</dt>
		<dd>', !empty($category['description']) ? '
			<p class="b_description">' . $category['description'] . '</p>' : '', '		
		</dd>';
	}
	echo '
	</dl>
</div>';
}
function aside_buttons()
{
	global $context;

	// Show the mark all as read button?
	if ($context['user']['is_logged'] && !empty($context['categories']))
		b_button_strip($context['mark_read_button'], 'right');

}


/* show the boardlist */
function template_tab_boardlist()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="b_bi_boards" class="b_boardindex">
		';

	foreach ($context['categories'] as $category)
	{
		// If theres no parent boards we can see, avoid showing an empty category (unless its collapsed)
		if (empty($category['boards']) && !$category['is_collapsed'])
			continue;

		echo '
		<div id="b_cat_', $category['id'], '"> 
			<h3 class="b_cat ', $category['is_collapsed'] ? 'collapsed' : '', '">';

		// If this category even can collapse, show a link to collapse it.
		if ($category['can_collapse'])
			echo '
				<span id="b_upshrink_', $category['id'], '" class="b_bi_icons b_upshrink b_', $category['is_collapsed'] ? 'toggle_down' : 'toggle_up', '" data-collapsed="', (int) $category['is_collapsed'], '" title="', !$category['is_collapsed'] ? $txt['hide_category'] : $txt['show_category'], '"></span>';

		echo '
				', $category['link'], '
			</h3>
			<ul id="b_cat_', $category['id'], '_boards" class="b_bi_board', (!empty($category['css_class']) ? ' '.$category['css_class'] : ''), '"' ,$category['is_collapsed'] ? ' style="display: none;"' : '', '>';

		foreach ($category['boards'] as $board)
		{
			echo '
				<li>
				', function_exists('template_bi_' . $board['type'] . '_info') ? call_user_func('template_bi_' . $board['type'] . '_info', $board) : template_bi_board_info($board);

			// Won't somebody think of the children!
			if (function_exists('template_bi_' . $board['type'] . '_children'))
				call_user_func('template_bi_' . $board['type'] . '_children', $board);
			else
				template_bi_board_children($board);

			echo '
				</li>';
		}
		echo '
			</ul>
		</div>';
	}
	echo '
	</div>';
}
/* show only the boards, with additional info */
function template_tab_bdetail()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="b_details" class="b_icon_info">
		<div>
			<div id="b_details_sections">';
	foreach ($context['categories'] as $category)
	{
		// If theres no parent boards we can see, avoid showing an empty category (unless its collapsed)
		if (empty($category['boards']) && !$category['is_collapsed'])
			continue;

		foreach ($category['boards'] as $board)
		{
			echo '
				<section id="b_bdetail_' , $board['id'] , '">';
			// Has it outstanding posts for approval?
		if ($board['can_approve_posts'] && ($board['unapproved_posts'] || $board['unapproved_topics']))
			echo '
					<a href="', $scripturl, '?action=moderate;area=postmod;sa=', ($board['unapproved_topics'] > 0 ? 'topics' : 'posts'), ';brd=', $board['id'], ';', $context['session_var'], '=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="b_bi_icons b_moderate floatright"></a>';

		echo '
					<h2>' , $board['name'] , '</h2>
					<div class="board_description">', $board['description'], '</div>';

		// Show the "Moderators: ". Each has name, href, link, and id. (but we're gonna use link_moderators.)
		if (!empty($board['link_moderators']))
			echo '
					<p class="moderators">', count($board['link_moderators']) == 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']), '</p>';

		// Show the last post if there is one.
			echo'
					<div class="b_lastpost">
						', function_exists('bindex_bi_' . $board['type'] . '_lastpost') ? call_user_func('bindex_bi_' . $board['type'] . '_lastpost', $board) : bindex_bi_board_lastpost($board), '
					</div>
				</section>';
		}
	}
	echo '
			</div>
		</div>
	</div>';
}

function template_bi_board_icon($board)
{
	global $context, $scripturl;
	echo '
		<a href="', ($context['user']['is_guest'] ? $board['href'] : $scripturl . '?action=unread;board=' . $board['id'] . '.0;children'), '" class="b_bi_icons b_board_', $board['board_class'], '"', !empty($board['board_tooltip']) ? ' title="' . $board['board_tooltip'] . '"' : '', '></a>';
}

function template_bi_redirect_icon($board)
{
	global $context, $scripturl;
	echo '
		<a href="', $board['href'], '" class="b_bi_icons b_board_', $board['board_class'], '"', !empty($board['board_tooltip']) ? ' title="' . $board['board_tooltip'] . '"' : '', '></a>';
}

function template_bi_board_info($board)
{
	global $context, $scripturl, $txt;
	echo '
	<span class="b_binfo">
		<span data-item="b_bdetail_' , $board['id'] , '" class="b_binfo_text b_', $board['board_class'], '">	
			<a href="', $board['href'], '" id="b', $board['id'], '">', $board['name'], '</a>';

	if ($board['can_approve_posts'] && ($board['unapproved_posts'] || $board['unapproved_topics']))
		echo '
			<a href="', $scripturl, '?action=moderate;area=postmod;sa=', ($board['unapproved_topics'] > 0 ? 'topics' : 'posts'), ';brd=', $board['id'], ';', $context['session_var'], '=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="b_bi_icons b_moderate"></a>';

	// Show some basic information about the number of posts, etc.
	echo '
			', function_exists('template_bi_' . $board['type'] . '_stats') ? call_user_func('template_bi_' . $board['type'] . '_stats', $board) : template_bi_board_stats($board),'
			', function_exists('template_bi_' . $board['type'] . '_lastpost') ? call_user_func('template_bi_' . $board['type'] . '_lastpost', $board) : template_bi_board_lastpost($board),'
			', function_exists('template_bi_' . $board['type'] . '_icon') ? call_user_func('template_bi_' . $board['type'] . '_icon', $board) : template_bi_board_icon($board), '
		</span>
		<p class="b_description">', $board['description'], '</p>
	</span>';

}

function template_bi_board_stats($board) {
	echo $board['posts'], ' | ', $board['topics'];
}

function template_bi_redirect_stats($board) {
	echo $board['posts'];
}

function template_bi_board_lastpost($board) {
	if (!empty($board['last_post']['id']))
		echo '
		<a href="' , $board['last_post']['href'] , '" class="b_bi_icons b_lastpost_link"></a>';
}

function bindex_bi_board_lastpost($board) {
	if (!empty($board['last_post']['id']))
		echo '
	<a href="' , $board['last_post']['member']['href'] , '" class="b_avatar_board" title="' , $board['last_post']['member']['name'] , '" style="background-image: url(' , $board['last_post']['member']['avatar']['href'] , ');"></a>
	<div>', $board['last_post']['last_post_message'], '</div>';
}

/**
 * Outputs the board children for a standard board.
 *
 * @param array $board Current board information.
 */
function template_bi_board_children($board)
{
	global $txt, $scripturl, $context;

	// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
	if (!empty($board['children']))
	{
		$children = array();
		foreach ($board['children'] as $child)
		{
			// Has it posts awaiting approval?
			if ($child['can_approve_posts'] && ($child['unapproved_posts'] || $child['unapproved_topics']))
				$child['link'] .= ' <a href="' . $scripturl . '?action=moderate;area=postmod;sa=' . ($child['unapproved_topics'] > 0 ? 'topics' : 'posts') . ';brd=' . $child['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '" class="b_moderate"></a>';

			$children[] = '<span>' . (!empty($child['new']) ? '<a class="b_bi_icons b_board_on2" href="'.$child['last_post']['href'] . '"></a>' : '') . '<a href="' . $child['href'] . '">' . $child['name'] .' </a></span>';
		}
		echo '
			<ul id="board_', $board['id'], '_children" class="b_children">
				<li>', implode('</li><li>', $children), '</li>
			</ul>';
	}
}

function template_boardindex_outer_below()
{
	// not much to do here either...
}

/**
 * The recent posts section of the info center
 */
function template_ic_block_recent()
{
	global $context, $scripturl, $settings, $txt;

	// This is the "Recent Posts" bar.
	echo '
			<h4>
				<a href="', $scripturl, '?action=recent"><span class="b_icons b_recent_posts"></span> ', $txt['recent_posts'], '</a>
			</h4>
			<div id="b_recent_posts">';

	// Only show one post.
	if ($settings['number_recent_posts'] == 1)
	{
		// latest_post has link, href, time, subject, short_subject (shortened with...), and topic. (its id.)
		echo '
				<p>
					<a href="', $scripturl, '?action=recent">', $txt['recent_view'], '</a> ', sprintf($txt['is_recent_updated'], '&quot;' . $context['latest_post']['link'] . '&quot;'), ' (', $context['latest_post']['time'], ')<br>
				</p>';
	}
	// Show lots of posts.
	elseif (!empty($context['latest_posts']))
	{
		echo '
				<div class="b_recent">
		';
		foreach ($context['latest_posts'] as $post)
			echo '
					<ul>
						<li class="b_recentpost">', $post['link'], '</li>
						<li class="b_recentposter">', $post['poster']['link'], '</li>
						<li class="b_recentboard">', $post['board']['link'], '</li>
						<li class="b_recenttime">', $post['time'], '</li>
					</ul>';
		echo '
				</div>';
	}
	echo '
			</div>';
}

/**
 * The calendar section of the info center
 */
function template_ic_block_calendar()
{
	global $context, $scripturl, $txt;

	// Show information about events, birthdays, and holidays on the calendar.
	echo ' 
			<h4>
				<a href="', $scripturl, '?action=calendar' . '"><span class="b_icons b_calendar"></span> ', $context['calendar_only_today'] ? $txt['calendar_today'] : $txt['calendar_upcoming'], '</a>
			</h4>';

	// Holidays like "Christmas", "Chanukah", and "We Love [Unknown] Day" :P
	if (!empty($context['calendar_holidays']))
		echo '  
			<p class="holiday">
				<span>', $txt['calendar_prompt'], '</span> ', implode(', ', $context['calendar_holidays']), '
			</p>';

	// People's birthdays. Like mine. And yours, I guess. Kidding.
	if (!empty($context['calendar_birthdays']))
	{
		echo '
			<p>
				<span class="birthday">', $context['calendar_only_today'] ? $txt['birthdays'] : $txt['birthdays_upcoming'], '</span>';

		// Each member in calendar_birthdays has: id, name (person), age (if they have one set?), is_last. (last in list?), and is_today (birthday is today?)
		foreach ($context['calendar_birthdays'] as $member)
			echo '
				<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['is_today'] ? '<strong class="fix_rtl_names">' : '', $member['name'], $member['is_today'] ? '</strong>' : '', isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>', $member['is_last'] ? '' : ', ';

		echo '
			</p>';
	}

	// Events like community get-togethers.
	if (!empty($context['calendar_events']))
	{
		echo '
			<p>
				<span class="event">', $context['calendar_only_today'] ? $txt['events'] : $txt['events_upcoming'], '</span> ';

		// Each event in calendar_events should have:
		//		title, href, is_last, can_edit (are they allowed?), modify_href, and is_today.
		foreach ($context['calendar_events'] as $event)
			echo '
				', $event['can_edit'] ? '<a href="' . $event['modify_href'] . '" title="' . $txt['calendar_edit'] . '"><span class="main_icons calendar_modify"></span></a> ' : '', $event['href'] == '' ? '' : '<a href="' . $event['href'] . '">', $event['is_today'] ? '<strong>' . $event['title'] . '</strong>' : $event['title'], $event['href'] == '' ? '' : '</a>', $event['is_last'] ? '<br>' : ', ';
		echo '
			</p>';
	}
}

/**
 * The stats section of the info center
 */
function template_ic_block_stats()
{
	global $scripturl, $txt, $context, $settings;

	// Show statistical style information...
	echo '
			<h4>
				<a href="', $scripturl, '?action=stats" title="', $txt['more_stats'], '"><span class="b_icons b_stats"></span> ', $txt['forum_stats'], '</a>
			</h4>
			<p>
				', $context['common_stats']['boardindex_total_posts'], '', !empty($settings['show_latest_member']) ? ' - ' . $txt['latest_member'] . ': <strong> ' . $context['common_stats']['latest_member']['link'] . '</strong>' : '', '<br>
				', (!empty($context['latest_post']) ? $txt['latest_post'] . ': <strong>&quot;' . $context['latest_post']['link'] . '&quot;</strong>  (' . $context['latest_post']['time'] . ')<br>' : ''), '
				<a href="', $scripturl, '?action=recent">', $txt['recent_view'], '</a>
			</p>';
}

/**
 * The who's online section of the info center
 */
function template_ic_block_online()
{
	global $context, $scripturl, $txt, $modSettings, $settings;
	// "Users online" - in order of activity.
	echo '
			<h4>
				', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', '<span class="b_icons b_people"></span> ', $txt['online_users'], '', $context['show_who'] ? '</a>' : '', '
			</h4>
			<p>
				', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', '<strong>', $txt['online'], ': </strong>', comma_format($context['num_guests']), ' ', $context['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ', comma_format($context['num_users_online']), ' ', $context['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Handle hidden users and buddies.
	$bracketList = array();

	if ($context['show_buddies'])
		$bracketList[] = comma_format($context['num_buddies']) . ' ' . ($context['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies']);

	if (!empty($context['num_spiders']))
		$bracketList[] = comma_format($context['num_spiders']) . ' ' . ($context['num_spiders'] == 1 ? $txt['spider'] : $txt['spiders']);

	if (!empty($context['num_users_hidden']))
		$bracketList[] = comma_format($context['num_users_hidden']) . ' ' . ($context['num_spiders'] == 1 ? $txt['hidden'] : $txt['hidden_s']);

	if (!empty($bracketList))
		echo ' (' . implode(', ', $bracketList) . ')';

	echo $context['show_who'] ? '</a>' : '', '

				&nbsp;-&nbsp;', $txt['most_online_today'], ': <strong>', comma_format($modSettings['mostOnlineToday']), '</strong>&nbsp;-&nbsp;
				', $txt['most_online_ever'], ': ', comma_format($modSettings['mostOnline']), ' (', timeformat($modSettings['mostDate']), ')<br>';

	// Assuming there ARE users online... each user in users_online has an id, username, name, group, href, and link.
	if (!empty($context['users_online']))
	{
		echo '
				', sprintf($txt['users_active'], $modSettings['lastActive']), ': ', implode(', ', $context['list_users_online']);

		// Showing membergroups?
		if (!empty($settings['show_group_key']) && !empty($context['membergroups']))
			echo '
				<span class="membergroups">' . implode(', ', $context['membergroups']) . '</span>';
	}

	echo '
			</p>';
}

// not actually used, but for reference in case something else calls it
function template_info_center()
{
	return;
}

// the infocenter part
function template_tab_ic()
{
	global $context, $options, $txt, $settings;

	if (empty($context['info_center']))
		return;

	// Here's where the "Info Center" starts...
	echo '
	<div id="b_bi_infocenter">';

	foreach ($context['info_center'] as $block)
	{
		if($block['tpl'] != 'recent') 
		{
			$func = 'template_ic_block_' . $block['tpl'];
			echo '
		<section class="b_bi_infocenter_items b_ic_' . $block['tpl'] . '">
			' , $func() , '
		</section>';
		}
	}
	echo '
	</div>';
}

function template_tab_ic_selected() 
{
	global $settings;

	echo '
	<div id="b_bi_infocenter_selected">
		<section class="b_bi_infocenter_items b_ic_recent">
			' , template_ic_block_recent() , '
		</section>
	</div>';

}

?>