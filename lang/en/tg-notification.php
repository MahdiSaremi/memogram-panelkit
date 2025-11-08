<?php

return [
    'request_send_message'    => "Send your message:",
    'end_send_message'        => "Public message queued",
    'request_forward_message' => "Send your message for public forwarding:",
    'end_forward_message'     => "Public forwarding queued",

    'log' => [
        'template' => <<<TEXT
            :title


            :all
            :success
            :failed

            :progress
            TEXT,

        'title' => [
            'created'     => "♻️ Public message sending started",
            'progressing' => "♻️ Public message sending...",
            'completed'   => "✅ Public message completed",
            'failed'      => "⚠️ Public message encountered an error",
        ],

        'counter' => [
            'all'     => "⚪ :number Of :all",
            'success' => "🟢 :number Successful",
            'failed'  => "🔴 :number Unsuccessful",
        ],

        'error_template' => <<<TEXT
            ⚠️ An error occurred in the (public) messaging system.


            :message
            TEXT,
    ],
];
