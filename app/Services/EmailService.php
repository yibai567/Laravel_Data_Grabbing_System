<?php

namespace App\Services;

use Mail;
use Log;
use Exception;

class EmailService extends Service {

    /**
     * send
     *
     * @param   email
     * @param   tpl
     * @return void
     */
    public function send($email, $content, $tpl) {
        try{
            $title = 'Stop Server';
            $nickname = explode('@', $email);
            Mail::send($tpl, ['name' => $nickname[0], 'content' => $content],function($message) use ($email, $title) {
                $message->to($email)->subject($title);
            });
        } catch (Exception $e) {
            Log::error('[EmailService send] send email failed,message = ' . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * sendToQueue
     *
     * @param   email
     * @param   tpl
     * @return void
     */
    public function sendToQueue($email, $tpl) {

        return true;
    }

}
