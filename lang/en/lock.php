<?php

return [
    'error'          => "⚠️ To work with the bot, you must first become a member of the following channels\n\nAfter completing the operation, click the \":submit\" button",
    'submit'         => "✅ Done",
    'submit_invalid' => "🔴 There are still channels you haven't subscribed to.",

    'groups' => [
        'main' => "Main",
    ],

    'resource' => [
        'list'                    => "Lock list:",
        'info'                    => "Lock info:",
        'toggle_group'            => "Group : :group",
        'info_group'              => "Group :",
        'info_url'                => "Url :",
        'info_title'              => "Title :",
        'info_expire'             => "Expire At :",
        'info_cache'              => "Cache :",
        'info_member_limit'       => "Expire In :",
        'form_group'              => "Select a group:",
        'form_select'             => "Send the public or numeric channel/group ID, or forward one of the channel messages:",
        'form_select_text_error'  => "Only public ID and numeric ID are acceptable",
        'form_select_admin_error' => "The robot is not a channel admin",
        'form_url'                => "Send membership link:",
        'form_url_error'          => "Invalid format",
        'form_title'              => "Enter a title:",
        'form_cache'              => "Enter cache time:",
        'form_member_limit'       => "When it reaches how many members will it be removed from the list?",
    ],

    'counter' => [
        'seconds' => ":value Seconds",
        'members' => ":value Members",
    ],
];
