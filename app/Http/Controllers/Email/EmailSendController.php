<?php

declare(strict_types=1);

namespace App\Http\Controllers\Email;

use App\Http\Controllers\MainController;
use App\Mail\GenericEmail;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailSendController extends MainController
{
    /**
     * Save email in queue
     * Change email status to QUEUE (Pending)
     */
    public function send(Request $request, int $id)
    {
        $email = Email::findOrFail($id);
        $email->status = Email::QUEUE;
        $email->save();
        $params['body'] = $email->body;

        if (isset($email->signature)) {
            $params['signature'] = Auth::user()->signature_html;
        }

        /**
         * @TODO Refactor this as a Service
         */
        $message = new GenericEmail(Auth::user()->company, $email->subject, $params);
        try {
            $mail = Mail::to($email->to);
            if ($email->cc) {
                $mail->cc($email->cc);
            }

            if ($email->attachments()->count() > 0) {
                foreach ($email->attachments() as $attachment) {
                    $file = Attachment::fromPath(storage_path('app/'.$attachment->file))
                        ->as($attachment->original_name)->withMime($attachment->mime);
                    $message->attach($file);
                }
            }
            $mail->send($message);
            $email->status = Email::SENT;
        } catch (\Throwable $t) {
            Log::error($t->getMessage());
            $email->status = Email::ERROR;
        }
        $email->save();

        return redirect('/email');
    }
}
