<?php
namespace app;

class mailer {

    public function __construct () {}

    public function send ($to, $subject, $message, $from, $file = null) {

        $semi_rand = md5(time()); 
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

        // TODO read From: from config, somehow
        $headers    = 'From: ' . $from . chr(10)
        . 'MIME-Version: 1.0' . chr(10)
        . 'Content-Type: multipart/mixed;' . chr(10)
        . ' boundary="' . $mime_boundary . '"';

        $message    = '--' . $mime_boundary . chr(10)
        . 'Content-Type: text/html; charset=UTF-8' . chr(10)
        . 'Content-Transfer-Encoding: 8bit' . chr(10) . chr(10)
        . $message . chr(10) . chr(10);

        // attach file
        if ($file) {
            $message .= '--'  . $mime_boundary . chr(10);

            $data = chunk_split(base64_encode(file_get_contents($file)));
            $message    .= 'Content-Type: application/octet-stream; name="' . basename($file) . '"' . chr(10)
            . 'Content-Description: ' . basename($file) . chr(10)
            . 'Content-Disposition: attachment;' . chr(10)
            . ' filename="' . basename($file) . '"; size="' . filesize($file) . '";' . chr(10)
            . 'Content-Transfer-Encoding: base64' . chr(10) . chr(10)
            . $data . chr(10) . chr(10);
        }
        $message .= '--' . $mime_boundary . '--';

        $subject    = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        mail($to, $subject, $message, $headers);
    }
}
