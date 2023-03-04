<?php

namespace App\Notifications;

use NotificationChannels\Hubtel\HubtelChannel;
use NotificationChannels\Hubtel\HubtelMessage;
use Illuminate\Bus\Queueable;
use App\Models\Company;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Efriandika\LaravelSettings\Facades\Settings;

class SendSMS extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;
    public $request;
    public $school;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $request = null)
    {
        $this->user = $user;
        $this->request = $request->all();
        $this->school = Company::find(session('current_company'));;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        /*return ['mail', HubtelChannel::class];*/

        return [HubtelChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

    public function toMail($notifiable)
    {
        /*return (new MailMessage)
            ->subject($this->school->title)
            ->greeting('Hello'.' '.$this->user->full_name.'')
            ->line($this->request['text'])
            ->action('Click here to visit your portal', url('/'))
            ->line('Thank you');*/
    }

    public function toSMS($notifiable)
    {
        return (new HubtelMessage)
            ->from($this->school->sms_name)
            ->content($this->request['text']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
