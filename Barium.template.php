<?php

/* show the boardlist */
function template_tab_boardlist_board($board)
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

?>