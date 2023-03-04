<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\NexmoMessage;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LoginNotification extends Notification implements ShouldQueue
{
    use Queueable;


    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
        /*$this->school = Company::find(session('current_company'));;*/
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'nexmo'];
    }



    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */


    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            /*->from($this->school->email, $this->school->title)*/
            ->subject('Login Notification')
            ->greeting('Hello '.$this->user->full_name.'')
            ->line('Your Account just logged in to this application.')
            ->action('Go to application', url('/'))
            ->line('If you did not login, kindly contact your administrator');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
            ->content('Dear '.$this->user->full_name.', Your Account just logged in to '.url('/').'. If you did not login, kindly contact your administrator');
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
